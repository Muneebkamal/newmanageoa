<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Models\BundleItem;
use App\Models\CashBack;
use App\Models\EventLog;
use App\Models\LineItem;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\ShipEvent;
use App\Models\User;
use App\Models\UserEmail;
use App\Services\OrderScraper;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Yajra\DataTables\Facades\DataTables;
use GuzzleHttp\Cookie\CookieJar;

use function PHPSTORM_META\map;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!\Auth::user()->can('view_orders')) {
            abort(403);
        }
         // Get counts for each order status
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

        // Prepare counts for your dropdown
        $counts = [
            'all' => Order::count(),
            'draft' => $statusCounts['draft'] ?? 0,
            'ordered' => $statusCounts['ordered'] ?? 0,
            'partially_received' => $statusCounts['partially received'] ?? 0,
            'reconcile' => $statusCounts['reconcile'] ?? 0,
            'received_in_full' => $statusCounts['received in full'] ?? 0,
            'closed' => $statusCounts['closed'] ?? 0,
            'canceled' => $statusCounts['canceled'] ?? 0,
        ];

        return view('orders.index',get_defined_vars());
    }


    public function getOrders(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::select([
                'id',
                'status',
                'order_id',
                'source',
                'date',
                'total',
                'created_at',
                'total_units_purchased',
                'total_units_received',
                'total_units_shipped',
                'unit_errors'
            ])->with('LineItems');
            if (isset($request->type) && $request->type === 'dashboard') {
                $orders->whereDate('date', '>=', Carbon::now()->subDays(6));

            }
            
            // Apply filtering based on status
            if ($request->status && $request->status !== 'all') {
                $orders->where('status', $request->status); // Adjust case sensitivity if necessary
            }
            // Apply sorting based on selected fields
            if ($request->sortBy) {
                $orderType = $request->orderType === 'desc' ? 'desc' : 'asc';

                $orders->orderBy($request->sortBy, $orderType);
            } else {
                $orders->orderBy('date', 'desc'); // Default sorting by 'created_at' descending
            }
            $details = $orders->pluck('id');
            // dd($orders->with('LineItems')->get());
           
            return DataTables::of($orders)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $searchTerm = $request->search['value'];
                    $query->where(function ($subQuery) use ($searchTerm) {
                        // Search in the Order table columns
                        $subQuery->whereRaw('LOWER(order_id) LIKE ?', [strtolower("%{$searchTerm}%")])
                                 ->orWhereRaw('LOWER(status) LIKE ?', [strtolower("%{$searchTerm}%")])
                                 ->orWhereRaw('LOWER(source) LIKE ?', [strtolower("%{$searchTerm}%")]);
            
                        // Search in the LineItems relationship
                        $subQuery->orWhereHas('LineItems', function ($lineItemQuery) use ($searchTerm) {
                            $lineItemQuery->where(function ($q) use ($searchTerm) {
                                $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%{$searchTerm}%")])
                                  ->orWhereRaw('LOWER(asin) LIKE ?', [strtolower("%{$searchTerm}%")])
                                  ->orWhereRaw('LOWER(supplier) LIKE ?', [strtolower("%{$searchTerm}%")]);
                            });
                        });
                    });
                }
            })
            ->editColumn('status', function($order) {
                $badgeClasses = [
                    'draft' => 'bg-primary-subtle text-primary',
                    'ordered' => 'bg-info text-light',
                    'partially received' => 'bg-warning text-dark',
                    'received in full' => 'bg-success text-light',
                    'reconcile' => 'bg-secondary text-light',
                    'closed' => 'bg-dark text-light',
                ];
            
                $badgeClass = isset($badgeClasses[$order->status]) ? $badgeClasses[$order->status] : 'bg-secondary';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($order->status) . '</span>';
            })
            ->editColumn('order_id', function($order) {
                return '<a href="/order/' . $order->id . '" target="_blank">' . $order->order_id . '</a>';
            })
            ->editColumn('source', function($order) {
                $parse ='';
                if( $order->source != null){
                    $parse = parse_url($order->source);
                    if(isset($parse['host'])){
                         $parse = $parse['host'];
                    }else{
                         $parse = $order->source;
                    }
                   
                }
                   
                return '<a href="'.$order->source.'">' . $parse . '</a>';
            })
            ->editColumn('order_item_count', function ($order) {
                $ordered = $order->total_units_purchased != 0 
                    ? '<span class="badge bg-dark me-3" data-bs-toggle="tooltip" title="Ordered">'.$order->total_units_purchased.'</span>' 
                    : '<span class="me-3" data-bs-toggle="tooltip" title="Ordered">-</span>';

                $received = $order->total_units_received != 0 
                    ? '<span class="badge bg-info me-3" data-bs-toggle="tooltip" title="Received">'.$order->total_units_received.'</span>' 
                    : '<span class="me-3" data-bs-toggle="tooltip" title="Received">-</span>';

                $shipped = $order->total_units_shipped != 0 
                    ? '<span class="badge bg-success me-3" data-bs-toggle="tooltip" title="Shipped">'.$order->total_units_shipped.'</span>' 
                    : '<span class="me-3" data-bs-toggle="tooltip" title="Shipped">-</span>';

                $errors = $order->unit_errors != 0 
                    ? '<span class="badge bg-danger me-3" data-bs-toggle="tooltip" title="Error">'.$order->unit_errors.'</span>' 
                    : '<span class="me-3" data-bs-toggle="tooltip" title="Error">-</span>';

            
                return '
                    <div class="d-flex flex-wrap justify-content-start" style="cursor: pointer;">
                        ' . $ordered . '
                        ' . $received . '
                        ' . $shipped . '
                        ' . $errors . '
                    </div>';
            })
            ->editColumn('date', function($order) {
                return Carbon::parse($order->date) ->format('M d, Y');
            })
            ->editColumn('total', function($order) {
                return '$' . number_format($order->total, 2);
            })
            ->addColumn('actions', function($order) {
                return '
                    <a style="cursor: pointer;" href="/order/' . $order->id . '" class="btn btn-outline-info">
                        <i class="ri-folder-open-fill" aria-hidden="true"></i>
                    </a>
                    <div class="btn-group dropleft">
                        <i style="cursor: pointer;" class="border mdi mdi-dots-vertical fs-5 ms-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#exampleModalScrollable" onclick="duplicateOrder(' . $order->id . ')">
                                <i class="ri-pencil-line text-primary me-2"></i>Duplicate ORder
                            </a>
                            <a class="dropdown-item" style="cursor: pointer;" onclick="deleteOrder(' . $order->id . ')">
                                <i class="ri-delete-bin-line text-danger me-2"></i>Delete Order
                            </a>
                        </div>
                    </div>';
            })
            ->setRowAttr([
                'data-href' => function($order) {
                    return url('/order/' . $order->id); // Set the URL as a data attribute for the row
                },
            ])
            ->rawColumns(['status','order_id','source', 'order_item_count','date', 'total','actions'])
            ->with('orderIds', $details)
            ->make(true);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!\Auth::user()->can('view_orders')) {
            abort(403);
        }
         // Create a new order without any specific data
         $order = Order::create([
            // Set default values for the order, if needed
            'status' => 'draft', // Example default value
            'date' => Carbon::now(), // Example default value
        ]);

        // Return a success response with the order ID
        return response()->json([
            'success' => true,
            'orderId' => $order->id,
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!\Auth::user()->can('view_orders')) {
            abort(403);
        }
        $order = Order::where('id',$id)->first();
        $emails = UserEmail::latest('created_at')->get();
        $locations = Location::latest('created_at')->get();
        $cashback = CashBack::latest('created_at')->get();
        return view('orders.create-order',get_defined_vars());
    }
    public function orderDetail(string $id)
    {
        if (!\Auth::user()->can('view_orders')) {
            abort(403);
        }
        $emails = UserEmail::latest('created_at')->get();
        $locations = Location::latest('created_at')->get();
        $order = Order::where('id',$id)->with('createdBy')->first();
        $buyers = User::where('role_id',3)->get();
        $cashback = CashBack::latest('created_at')->get();
        return view('orders.order-detail',get_defined_vars());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Order::where('id',$id)->first();
        if($data){
            LineItem::where('order_id', $data->id)->delete();
            $data->delete();
            return response()->json([
                'success' => true,
                'message'=> 'Order delete successfully!'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message'=> 'Order delete successfully!'
            ]);
        }
        
    }
    public function duplicateOrder(string $id)
    {
        // Fetch the original order
        $data = Order::where('id', $id)->first();
        if ($data) {
            // Duplicate the order (excluding its ID)
            $newOrder = $data->replicate();
            $newOrder->order_id = $newOrder->order_id.' (copy)';
            $newOrder->save(); // Save the new order (this generates a new ID)
            // Duplicate the line items related to the original order
            $lineItems = LineItem::where('order_id', $data->id)->get();
            foreach ($lineItems as $lineItem) {
                // Replicate each line item
                $newLineItem = $lineItem->replicate();
                $newLineItem->order_id = $newOrder->id; // Set the new order ID
                $newLineItem->save(); // Save the new line item
                // Fetch the bundles associated with the current line item
                $bundles = BundleItem::where('item_id', $lineItem->id)->get();
                // Duplicate the bundles for the new line item (only update item_id)
                foreach ($bundles as $bundle) {
                    // Create a new bundle for the duplicated line item
                    $newBundle = $bundle->replicate(); // Replicate the bundle data
                    $newBundle->item_id = $newLineItem->id; // Set the new line item ID
                    $newBundle->save(); // Save the new bundle
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order and associated items duplicated successfully!',
                'new_order_id' => $newOrder->id // Optionally return the ID of the new order
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found!'
            ]);
        }        
    }
    // In web.php (routes file)

    // In OrderController.php
    public function saveOrderItem(Request $request)
    {
        $data = $request->all();
        // Save the item to the database
        $data['sku_total'] = $data['buy_cost'] * $data['unit_purchased'];
        $data['total_units_purchased'] = $data['unit_purchased'];
        // Save the item to the database
        
        $orderItem = LineItem::create($data);
        if($orderItem->order_id != null){
            $findOrder = Order::where('id',$orderItem->order_id)->first();
            if( $findOrder){
                $unitPurchased = $data['unit_purchased'];
                $buyCost = $data['buy_cost'];
                $findOrder->total_units_purchased += $unitPurchased;
                $findOrder->total += ($buyCost * $unitPurchased);
                $findOrder->total = number_format($findOrder->total, 2, '.', '');
                $findOrder->save();
                $totalItems = $findOrder->total_units_purchased;
                $totalBuyCost = $findOrder->total_buy_cost;  // Option
                // $findOrder->total_units_purchased = $findOrder->total_units_purchased +  $data['unit_purchased'];
                // $findOrder->save();
                // $totalItems = $findOrder->total_units_purchased;
            }
        }
        return response()->json([
            'success' => true,
            'message'=> 'Order and item saved successfully!'
        ]);
    }
    public function addItems(Request $request)
    {
        $data = $request->all();
        $data['sku_total'] = $data['buy_cost'] * $data['unit_purchased'];
        $data['total_units_purchased'] = $data['unit_purchased'];
        $data['is_hazmat']= $data['isHazmat']==true?1:0;
        $data['is_disputed']= $data['isDisputed']==true?1:0;
        // Save the item to the database
        $orderItem = LineItem::create($data);
        if($orderItem->order_id != null){
            $findOrder = Order::where('id',$orderItem->order_id)->first();
            if( $findOrder){
                $findOrder->total_units_purchased = $findOrder->total_units_purchased +  $data['unit_purchased'];
                $unitPurchased = $data['unit_purchased'];
                $buyCost = $data['buy_cost'];
                $findOrder->total_units_purchased += $unitPurchased;
                $findOrder->total += ($buyCost * $unitPurchased);
                $findOrder->total = number_format($findOrder->total, 2, '.', '');
                $findOrder->save();
                $totalItems = $findOrder->total_units_purchased;
            }
        }
        return response()->json([
            'success' => true,
            'message'=> 'item saved successfully!',
            'total_items'=>$totalItems
        ]);
    }
    public function getOrderItems(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
        ]);

        // Retrieve all line items for the given order ID
        $items = LineItem::where('order_id', $validated['order_id'])->where('is_rejected',0)->with('bundles')->get();

        // Return a JSON response with the items
        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }
    public function getOrderItemsDashboard(Request $request)
    {
        $orderIds = $request->order_ids;
        if (!$orderIds || empty($orderIds)) {
            return response()->json([]);
        }
        // Retrieve all line items for the given order ID
        $items = LineItem::whereIn('order_id', $orderIds)->where('is_rejected',0)->with('bundles')->get() ;
        // Return a JSON response with the items
        return response()->json( $items);
    }
    public function updateOrderItem(Request $request) {
        $item = LineItem::find($request->id);
        if ($item) {
            $item->unit_purchased = $request->unit_purchased;
            $item->total_units_purchased = $request->unit_purchased;
            $item->sku_total = $request->sku_total;
            $item->buy_cost = $request->buy_cost;
            $item->save();
    
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Item not found']);
    }
    
    public function deleteOrderItem(Request $request) {
        $item = LineItem::find($request->id);
        if ($item) {
            $item->delete();
    
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Item not found']);
    }
    public function uploadFiles(Request $request)
    {
        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('uploads', 'public');
                
                // Save file info in the order_files table
                $orderFile = new OrderFile;
                $orderFile->name = $request->display_name;
                $orderFile->note = $request->note;
                $orderFile->path = $path;
                $orderFile->created_by = auth()->user()->name;
                $orderFile->save();

                // Prepare file details for response
                $uploadedFiles[] = [
                    'id' => $orderFile->id,
                    'name' => $orderFile->name,
                    'note' => $orderFile->note,
                    'path' => $path,
                ];
            }
        }

        // Return uploaded files as JSON
        return response()->json($uploadedFiles);
    }
    public function attchmentList(Request $request)
    {
        $files = OrderFile::where('order_id',$request->order_id)->get();

        $fileData = $files->map(function ($file) {
            return [
                'name' => $file->name,
                'note' => $file->note,
                'uploaded_by' => $file->uploaded_by,
                'id' => $file->id,
                'view_url' => $file->path,
                'download_url' => url('orderattachments.download', $file->id),
            ];
        });

        return response()->json($fileData);
    }
    public function uplladFile(Request $request){
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png|max:2048',
            'display_name' => 'required|string',
            'note' => 'nullable|string'
        ]);

        $file = $request->file('file');

        $order = OrderFile::create([
            'name' => $request->display_name,
            'order_id' => $request->order_id,
            'note' => $request->input('note'),
            'uploaded_by' => auth()->user()->name,
        ]);
        $path = $file->store('order_attachments/'.$request->order_id, 'public');
        $order->path = $path;
        $order->save();

        return response()->json(['success' => 'File uploaded successfully']);
    }
    public function updateFileInput(Request $request){
        $file = OrderFile::find($request->file_id);
        if ($file) {
            $file->name = $request->name;
            $file->note = $request->note;
            $file->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function deleteFile(Request $request)
    {
        $file = OrderFile::find($request->file_id);
        if ($file) {
            Storage::delete($file->path); // Remove file from storage
            $file->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
    public function saveOrdersUpdate(Request $request){
        $data = $request->all();
        unset($data['undefined']);
        unset($data['fileID']);
        if (!isset($data['total']) || !is_numeric($data['total'])) {
            $data['total'] = number_format(0, 2); // Format to 2 decimal places
        }
        if (!isset($data['sales_tax_rate']) || !is_numeric($data['sales_tax_rate'])) {
            $data['sales_tax_rate'] = number_format(0, 2); // Format to 2 decimal places
        }
        $order = Order::where('id',$data['id'])->first();
        if($order){
            $order->update($data);
            return response()->json(['success' => true]);
        }else{
            return response()->json(['success' => false]);
        }
    }
    public function saveOrderData(Request $request){
        $order = new Order;
        if($order){
            $order->note = $request->orderNote;
            $order->date = now();
            $order->status = 'ordered';
            $order->total = $request->cost * $request->unit_purchased;
            $order->total_units_purchased  = $request->unit_purchased;
            $order->source  = $request->source_url;
            $order->save();
            $orderItems = new LineItem;
            $orderItems->name = $request->name;
            $orderItems->order_id = $order->id;
            $orderItems->asin = $request->asin;
            $orderItems->msku = $request->msku;
            $orderItems->min = $request->min ?? 0;
            $orderItems->max = $request->max ?? 0;
            $orderItems->list_price = $request->list_price ?? 0;
            $orderItems->buy_cost = $request->buy_cost ?? 0;
            $orderItems->sku_total = $request->buy_cost ??0 * $request->unit_purchased ?? 1;
            $orderItems->unit_purchased = $request->unit_purchased ?? 1;
            $orderItems->product_buyer_notes = $request->orderNote;
            $orderItems->is_disputed = $request->isDisputed;
            $orderItems->is_hazmat = $request->isHazmat;
            $orderItems->source_url = $request->source_url;
            $orderItems->supplier = $request->supplier;
            $orderItems->save();
            return response()->json([
                'success'=>true,
                'id'=>$order->id
            ]);
        }else{
            return response()->json([
                'success'=>false,
            ]);
        }
        
    }
    public function saveOrderBundleItems(Request $request){

        $order = new Order;
        if($order){
            $order->note = $request->notes;
            $order->date = now();
            $order->status = 'ordered';
            $order->total = $request->cost * $request->unit_purchased;
            $order->subtotal = $request->cost * $request->unit_purchased;
            
            $order->total_units_purchased  = $request->unit_purchased;
            $order->save();
            $orderItems = new LineItem;
            $orderItems->name = $request->name;
            $orderItems->order_id = $order->id;
            $orderItems->asin = $request->asin;
            $orderItems->msku = $request->msku;
            $orderItems->min = $request->min ?? 0;
            $orderItems->max = $request->max ?? 0;
            $orderItems->list_price = $request->list_price ?? 0;
            $orderItems->buy_cost = $request->cost;
            $orderItems->sku_total = $request->cost * $request->unit_purchased;
            $orderItems->unit_purchased = $request->unit_purchased;
            $orderItems->product_buyer_notes = $request->product_buyer_notes;
            $orderItems->is_disputed = $request->is_disputed;
            $orderItems->is_hazmat = $request->is_hazmat;
            $orderItems->save();
            if($request->bundles != null){
                foreach($request->bundles as $bundle){
                    $newbundle = new BundleItem;
                    $newbundle->item_id = $orderItems->id;
                    $newbundle->name = $bundle['name'];
                    $newbundle->supplier = $bundle['supplier'];
                    $newbundle->source_url = $bundle['url'];
                    $newbundle->promo = $bundle['promo'];
                    $newbundle->cost = $bundle['cost'];
                    $newbundle->coupon_code = $bundle['coupon'];
                    $newbundle->save();
                }
            }
            return response()->json([
                'success'=>true,
                'id'=>$order->id
            ]);
        }else{
            return response()->json([
                'success'=>false,
            ]);
        }
    }
    public function saveLineItemTax(Request $request)
    {
        $lineItem = LineItem::findOrFail($request->item_id);
        $lineItem->pre_discount = $request->pre_discount;
        $lineItem->post_discount = $request->post_discount;
        $lineItem->shipping_tax = $request->shipping_tax;
        // $lineItem->total_cost = $request->total_cost;
        $lineItem->save();
        return response()->json(['success' => true, 'message' => 'Line item updated successfully']);
    }
    public function copyItemToBuyList($id){
        $find = LineItem::find($id);
        if($find){
            $newBuylist = $find->replicate(); // Create a copy of the item
            $newBuylist->order_id = null;
            $newBuylist->buylist_id = 1;
            $newBuylist->is_buylist = 1;
            $newBuylist->save();
            return response()->json(['success' => true, 'message' => 'Item copied to Team Buylist.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Someting Wrong.']);   
        }
    }
    public function saveEvents(Request $request){
       try{
            $data = $request->all();
            $data['received'] = $request->boolean('received') ? 1 : 0;
            $data['cc_charged'] = $request->boolean('cc_charged') ? 1 : 0;
            $data['cancelled'] = $request->boolean('cancelled') ? 1 : 0;
            $data['refunded'] = $request->boolean('refunded') ? 1 : 0;
            $findItem =  LineItem::where('id',$data['order_item_id'])->first();
            if ($findItem && isset($data['item_quantity'])) {
                // Increment the unit_error by adding the new item_quantity
                $findItem->unit_errors += $data['item_quantity'];
                $findItem->save(); // Save the updated value
            }
            $findOrder =  Order::where('id',$data['order_id'])->first();
            if ($findOrder && isset($data['item_quantity'])) {
                $findOrder->unit_errors += $data['item_quantity'];
                $findOrder->save(); // Save the updated value
            }
            $eventeLog = new EventLog;
            $eventeLog->create($data);
            return response()->json([
                'success'=>true,
                'message'=>'Event Added Successfully!',
                'status_code'=>200,
                'item_total_error'=> $findItem->unit_errors,
                'order_total_error'=> $findOrder->unit_errors,
                'order_item_id'=> $findItem->id,
            ]);
       }catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
                'status_code'=>400
            ]);
       }
    }
    public function getEventLogs($id,Request $request){
        if($request->type == 'order'){
            $evetns = EventLog::where('order_id',$id)->with('LineItem')->latest('created_at')->get();
            $shippingEvent = ShipEvent::where('order_id',$id)->with('shippingbatch','orderItem')->latest('created_at')->get();
        }else{
            $evetns = EventLog::where('order_item_id',$id)->with('LineItem')->latest('created_at')->get();
            $shippingEvent = ShipEvent::where('order_item_id',$id)->with('shippingbatch','orderItem')->latest('created_at')->get();
        }
        return response()->json(
            ['evetns'=>$evetns,
            'shippingEvent'=>$shippingEvent
            ]
        );
    }
    public function deleteEvent($id){
        
        $evetns = EventLog::where('id',$id)->first();
        $response = array();
        $findItem =  LineItem::where('id',$evetns->order_item_id)->first();
        if($evetns){
            if ($findItem ) {
                // Increment the unit_error by adding the new item_quantity
                $findItem->unit_errors -= $evetns->item_quantity;
                $findItem->save(); // Save the updated value
            }
            $findOrder =  Order::where('id',$evetns->order_id)->first();
            if ($findOrder) {
                $findOrder->unit_errors -= $evetns->item_quantity;
                $findOrder->save(); // Save the updated value
            }
            $evetns->delete();
            $response =[
                'message'=>'The issue event has been removed.',
                'success'=>true,
                'status_code'=>200,
                'item_total_error'=> $findItem->unit_errors,
                'order_total_error'=> $findOrder->unit_errors,
            ];
        }
        return response()->json($response);
    }
    public function exportOrders(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return Excel::download(
            new OrdersExport($startDate, $endDate),
            'orders.csv', // Correct file extension
            ExcelWriter::CSV // Explicitly set writer to CSV
        );

    }
    public function imprtOrder()
    {
       return view('importorder');
    }
    public function imprtORderNew(Request $request)
    {
       // Validate the uploaded file
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048', // Allow only CSV files up to 2MB
        ]);

        // Read the file directly without saving it
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), "r");
        $csvHeaders = fgetcsv($handle, 0, ",", '"'); // Read the first row as headers
        $csvData = [];
        while (($row = fgetcsv($handle, 0, ",", '"')) !== false) {
            if (!array_filter($row)) {
                continue; // Skip blank rows
            }
            $csvData[] = $row;
        }
        fclose($handle);
        
        $data = [];
        foreach ($csvData as $index => $row) {
            if ($index === 0) continue; // Skip the header row
            // Adjust the row to match the header length
            if (count($row) < count($csvHeaders)) {
                $row = array_pad($row, count($csvHeaders), null); // Pad with null for missing values
            } elseif (count($row) > count($csvHeaders)) {
                $row = array_slice($row, 0, count($csvHeaders)); // Truncate extra values
            }
            $data[] = array_combine($csvHeaders, $row); // Combine headers with data
        }

        foreach ($data as $item) {
            // Check if order already exists by 'Order #'
            $order_number = $item['Order #'];
            $order_number = preg_replace('/["=]/', '', $order_number);  // Remove "=" and quotes using regex
            $order = Order::where('order_id', $order_number)->first();
            // Check if the Destination already exists in the locations table
            $destination = $item['Destination'];
            // Search for the destination in the locations table
            $existingLocation = Location::where('location', $destination)->first();
            if (!$existingLocation) {
                // If it doesn't exist, add it to the locations table
                Location::create([
                    'location' => $destination,
                ]);
            }
            $orderNotes = $item['Order Notes'] ?? null;
            $userId = null; // Initialize the userId
            if ($orderNotes) {
                // Split the notes into words
                $words = explode(' ', $orderNotes);

                // Check each word against the users table
                $matchedUser = User::where(function ($query) use ($words) {
                    foreach ($words as $word) {
                        $query->orWhere('name', 'like', '%' . $word . '%');
                    }
                })->first();

                if ($matchedUser) {
                    $userId = $matchedUser->id; // Get the matched user's ID
                }
            }
            if ($order) {
                $totalUnitsPurchased = $order->total_units_purchased ?? 0;  // Assuming $previousTotal holds the previous value
                $totalUnitsPurchased += $item['Quantity Purchased'] ?? 0;

                // Merge (update) the existing order
                $order->update([
                    'date' => $item['date'],
                    'source' => $item['Host Link'],
                    'order_id' => $order_number,
                    'total' => $item['Order Total $'] ,
                    'subtotal' => $item['Order Total $'],
                    'card_used' => $item['Credit Card'],
                    'status' => $item['Order Status'],
                    'destination' => $item['Destination'],
                    'cash_back_percentage' => $item['Cash Back'] !== null ? $item['Cash Back'] * 100 : null,
                    'total_units_purchased' =>$totalUnitsPurchased ?? 0,
                    'note' => $item['Order Notes'] ?? null,
                    'buyer_id' =>  $userId ?? null,
                ]);
            } else {
                // Create a new order if not found
                $order = Order::create([
                    'date' => $item['date'],
                    'source' => $item['Host Link'],
                    'total_units_purchased' => $item['Quantity Purchased'] ?? 0,
                    'order_id' => $order_number,
                    'total' => $item['Order Total $'] ,
                    'subtotal' => $item['Order Total $'],
                    'cash_back_percentage' => $item['Cash Back'] !== null ? $item['Cash Back'] * 100 : null,
                    'card_used' => $item['Credit Card'],
                    'status' => $item['Order Status'],
                    'destination' => $item['Destination'],
                    'note' => $item['Order Notes'] ?? null,
                    'buyer_id' =>  $userId ?? null,
                ]);
            }
    
            // Create line items for the order
            LineItem::create([
                'order_id' => $order->id,  // Reference the order_id
                'name' => $item['name'] ?? null,
                'supplier' => $item['Supplier'],
                'source_url' => $item['Source Link'],
                'asin' => $item['AMZ ASIN'] ?? null,
                'unit_purchased' => $item['Quantity Purchased'] ?? 0,
                'total_units_purchased' => $item['Quantity Purchased'] ?? 0,
                'buy_cost' => $item['Cost Per Unit'] ?? 0,
                'sku_total' => $item['SKU Total'] ?? 0,
                'tax_paid' => $item['Tax Paid'] ?? 0,
                'tax_percent' => $item['Tax %'] ?? 0,
                'order_note' => $item['Order Notes'] ?? null,
                'product_buyer_notes' => $item['Item Notes'] ?? null,
                'price' => $item['Price'] ?? 0.00,
                'msku' => $item['MSKU'],
                'list_price' => !empty($item['List Price']) ? $item['List Price'] : 0.00,
                'min' => !empty($item['Min List Price']) ? $item['Min List Price'] : 0.00,
                'max' => !empty($item['Max List Price']) ? $item['Max List Price'] : 0.00,    
                'upc' => $item['UPC'],
            ]);
        }

        return back()->with('success', 'File uploaded and data imported successfully!');
    }


public function fetchOrders()
{

    // Initialize cURL session
    $ch = curl_init();

    // Set the URL of the Cheddar login page
    curl_setopt($ch, CURLOPT_URL, "https://app.oacheddar.com/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'username' => 'oacheddar@znzinc.com',
        'password' => '^*VFUXl!*1&7O'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt'); // Save cookies

    // Perform login
    $response = curl_exec($ch);
    dd( $response);

    // Access the orders page
    curl_setopt($ch, CURLOPT_URL, "https://your-cheddar-orders-page.com/orders");
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt'); // Use saved cookies
    $ordersPage = curl_exec($ch);

    // Parse the HTML for order data
    // $dom = new DOMDocument();
    // @$dom->loadHTML($ordersPage);
    // $xpath = new DOMXPath($dom);

    // // Example: Get all rows from an orders table
    // $rows = $xpath->query("//table[@id='orders-table']/tr");

    // foreach ($rows as $row) {
    //     $columns = $row->getElementsByTagName('td');
    //     foreach ($columns as $column) {
    //         echo $column->nodeValue . " ";
    //     }
    //     echo "\n";
    // }

    curl_close($ch);

}
public function assignedWorkOrder(Request $request){
     $lineItem = LineItem::where('id', $request->lineItemId)->first();
    
    if (!$lineItem) {
        return response()->json(['status' => false, 'message' => 'Line item not found'], 404);
    }
    
    $data = [
                'line_item_id' => $lineItem->id,
                'work_order_id' => $request->prepOrderId,
                'quantity' => $request->productQtyInput,
                'name' => $lineItem->name ?? 'Unnamed',
                'asin' => $lineItem->asin ?? 'SKU-' . time(),
                'buy_cost' => $lineItem->buy_cost ?? '0',
                'msku' => $lineItem->msku ?? null,
            ];
    
   $response = Http::asForm()->post('http://app.prepcenter.me/api/assign-work-order', $data);

    
    return $response->json();

    return $response->json();
}

}
