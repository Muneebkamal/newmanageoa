<?php

namespace App\Http\Controllers;

use App\Models\BundleItem;
use App\Models\Buylist;
use App\Models\Lead;
use App\Models\LineItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BuyerController extends Controller
{
    public function updateLineItemLeadIds(){
        $lineItems = LineItem::get();
        foreach($lineItems as $item){
            $findLead = Lead::where('asin',$item->asin)->first();
            if($findLead){
                $item->lead_id = $findLead->id;
                $item->save();
            }
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!\Auth::user()->can('view_buy_list')) {
            abort(403);
        }
        $sku = LineItem::where('is_buylist',1)->where('is_rejected',0)->count();
        $cost = LineItem::where('is_buylist',1)->where('is_rejected',0)->sum('buy_cost');
        $units = LineItem::where('is_buylist',1)->where('is_rejected',0)->sum('unit_purchased');
        return view('buyers.index',get_defined_vars());
    }
    public function index2()
    {
        if (!\Auth::user()->can('view_buy_list')) {
            abort(403);
        }
        $sku = LineItem::where('is_approved',1)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_rejected',0)->count();
        $cost = LineItem::where('is_approved',1)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_rejected',0)->sum('buy_cost');
        $units = LineItem::where('is_approved',1)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_rejected',0)->sum('unit_purchased');
        return view('buyers.index2',get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:buylists'
        ]);

        // Save the buylist to the database
        $buylist = Buylist::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true, 'message' => 'Buylist created successfully.']);
    }
    // BuylistController.php
    public function getBuylists()
    {
        if(auth()->user()->role_id == 1){
            $buylists = Buylist::all(); // Fetch all buylists from the database
            return response()->json($buylists);
        }else{
            $buylist = Buylist::where('employee_id',auth()->user()->id)->first(); // Fetch all buylists from the database
            if(empty( $buylist )){
                $newBuylist = new Buylist;
                $newBuylist->name = auth()->user()->first_name .' '. auth()->user()->last_name;
                $newBuylist->employee_id = auth()->user()->id;
                $newBuylist->creatd_by = auth()->user()->id;
                $newBuylist->save();
            }
            $buylists = Buylist::where('employee_id',auth()->user()->id)->get(); // Fetch all buylists from the database
            return response()->json($buylists);
        }
        
    }
    // Example Route in web.php

    // Controller Method
    // public function getItems($buylistId)
    // {
    //     $items = LineItem::where('buylist_id', $buylistId)->get(); // Adjust model as needed
    //     return response()->json($items);
    // }
    public function getItems($buylistId,Request $request){
        if($request->has('is_approved') && $request->is_approved != 'false'){
            if($request->has('is_rejected') && $request->is_rejected== 1){
                $items = LineItem::where('buylist_id', $buylistId)->where('is_rejected', 1)->where('is_approved',1)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
    
            }else{
                $items = LineItem::where('buylist_id', $buylistId)->where('is_rejected', 0)->where('is_approved',1)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
            }

        }else{
            if($request->has('is_rejected') && $request->is_rejected== 1){
                $items = LineItem::where('buylist_id', $buylistId)->where('is_rejected', 1)->where('is_approved',0)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
    
            }else{
                $items = LineItem::where('buylist_id', $buylistId)->where('is_rejected', 0)->where('is_approved',0)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
            }
        }
        if($request->start_date != null && $request->end_date != null){
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $items->whereBetween('created_at', [$startDate, $endDate]);
        }
        $is_rejected = $request->is_rejected;
        // Apply default sort only if no dynamic sorting is provided by DataTables
        if (!$request->has('order') || empty($request->order)) {
            $items->orderBy('created_at', 'desc');
        }
                
            // Get the count of rejected items
        $rejectedCount = LineItem::where('buylist_id', $buylistId)
        ->where('is_rejected', true)
        ->count();

        return DataTables::of($items)
            // ->editColumn('actions', function($order) use ($request) {
            //     $undoRejectButton = '<button class="btn btn-sm btn-success undoRejectItem py-0 px-1" data-id="' . $order->id . '">
            //         <i class="ri-arrow-go-back-line"></i> Undo Rejected
            //     </button>';

            //     if($request->has('is_approved') && $request->is_approved != 'false'){
            //         $isAprroved = '';
            //     } else {
            //         $isAprroved = '<button class="btn btn-sm btn-success approvelItem py-0 px-1" data-id="'.$order->id.'" data-viewonly="true">
            //             <i class="ri-check-fill"></i> Approve
            //         </button>';
            //     }

            //     $rejectButton = '<button class="btn btn-sm btn-danger rejectItem py-0 px-1" data-id="' . $order->id . '">
            //         <i class="ri-forbid-2-line"></i> Reject
            //     </button>';

            //     $deleteButton = '<button class="btn btn-sm btn-danger deleteItem py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-delete-bin-line"></i> Delete
            //     </button>';

            //     $editButton = '<button class="btn btn-sm btn-info editItem py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-pencil-fill"></i> Edit
            //     </button>';

            //     $duplicateButton = '<button class="btn btn-sm btn-info duplicateItem py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-file-copy-line"></i> Duplicate
            //     </button>';

            //     $moveCopyButton = '<button class="btn btn-sm btn-secondary moveCopy py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-share-forward-fill"></i> Move/Copy
            //     </button>';

            //     $orderButton = '<button class="btn btn-primary btn-block w-100 mt-1 singleOrder" data-id="'.$order->id.'">
            //         <i class="ri-shopping-cart-2-line"></i> Create Single Item Order
            //     </button>';

            //     return '
            //         <div class="d-flex flex-column align-items-start" style="max-width: auto;">
            //         <input type="checkbox" name="leadCheckBox" class="item-checkbox me-1" onchange="singleChecked('.$order->id.')" value="'.$order->id.'">
            //             <div class="d-flex flex-wrap gap-1 mb-1">
            //                 '.$isAprroved.'
            //                 '.$editButton.'
            //                 '.($order->is_rejected ? $undoRejectButton : $rejectButton).'
                            
            //                 '.$deleteButton.'
            //                 '.$duplicateButton.'
            //                 '.$moveCopyButton.'
            //             </div>
            //             '.$orderButton.'
            //         </div>
            //     ';
            // })
            ->addColumn('checkbox', function($order) {
                return '<input type="checkbox" class="item-checkbox" onchange="singleChecked('.$order->id.')" value="'.$order->id.'">';
            })
            ->editColumn('rejection_reason', function($order) {
                return  $order->rejection_reason;
            })
            // ->editColumn('flags', function($order) {
            //     $outPut = '';
            //     if($order->is_hazmat)
            //     $outPut .= '<i class="ri-alarm-warning-fill text-danger ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Hazmat item"></i>';
            //     if($order->is_disputed)
            //     $outPut .=' <i class="ri-haze-2-line text-warning ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Item data may be disputed"></i>';
            //     return $outPut;
            // })
            ->editColumn('created_at', function($order) {
                return  Carbon::parse($order->created_at) ->format('M d, Y');
            })
            ->editColumn('name', function($order) {
                $fullName = $order->name ?? '';
                $truncated = strlen($fullName) > 40 ? substr($fullName, 0, 40) . '...' : $fullName;

                return '<a href="#" title="'.e($fullName).'" data-bs-toggle="tooltip" data-bs-placement="top">'
                        . e($truncated) .
                    '</a>';
            })
            ->editColumn('asin', function($order) {
                return '<a href="https://www.amazon.com/dp/'.$order->asin.'" target="_blank">'.$order->asin.'</a>';
            })
            ->editColumn('source_url', function($order) {
                return '<a href="' . $order->source_url . '" target="_blank">' . $order->supplier . '</a>';
            })

            ->editColumn('unit_purchased', function($order) {
                return $order->unit_purchased ;
            })
            ->editColumn('buy_cost', function($order) {
                return  '$'.$order->buy_cost;
            })
            ->addColumn('approve', function($order) use ($request) {
                if ($request->has('is_approved') && $request->is_approved != 'false') {
                    return '';
                }
                return '<button class="btn btn-sm btn-success approvelItem" data-id="'.$order->id.'"  data-viewonly="true">
                            <i class="ri-check-fill"></i>
                        </button>';
            })
            ->addColumn('edit', fn($order) =>
                '<button class="btn btn-sm btn-info editItem" data-id="'.$order->id.'">
                    <i class="ri-pencil-fill"></i>
                </button>'
            )
            ->addColumn('reject', fn($order) =>
                $order->is_rejected
                    ? '<button class="btn btn-sm btn-success undoRejectItem" data-id="'.$order->id.'">
                        <i class="ri-arrow-go-back-line"></i>
                    </button>'
                    : '<button class="btn btn-sm btn-danger rejectItem" data-id="'.$order->id.'">
                        <i class="ri-forbid-2-line"></i>
                    </button>'
            )
            ->addColumn('delete', fn($order) =>
                '<button class="btn btn-sm btn-danger deleteItem" data-id="'.$order->id.'">
                    <i class="ri-delete-bin-line"></i>
                </button>'
            )
            ->addColumn('duplicate', fn($order) =>
                '<button class="btn btn-sm btn-info duplicateItem" data-id="'.$order->id.'">
                    <i class="ri-file-copy-line"></i>
                </button>'
            )
            ->addColumn('move_copy', fn($order) =>
                '<button class="btn btn-sm btn-secondary moveCopy" data-id="'.$order->id.'">
                    <i class="ri-share-forward-fill"></i>
                </button>'
            )
            ->addColumn('order', fn($order) =>
                '<button class="btn btn-sm btn-primary singleOrder" data-id="'.$order->id.'">
                    <i class="ri-shopping-cart-2-line"></i>
                </button>'
            )
            ->with('rejectedCount', $rejectedCount)
            ->rawColumns(['checkbox','approve','edit','reject','delete','duplicate','move_copy','order', 'rejection_reason','created_at','name','asin','source_url','unit_purchased','buy_cost',])
            ->make(true);
    }
    public function getItemsAll(Request $request){
        if($request->has('is_approved') && $request->is_approved != 'false'){
            if($request->has('is_rejected') && $request->is_rejected== 1){
                $items = LineItem::where('is_rejected', 1)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_approved',1)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
    
            }else{
                $items = LineItem::where('is_rejected', 0)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_approved',1)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
            }

        }else{
            if($request->has('is_rejected') && $request->is_rejected== 1){
                $items = LineItem::where('is_rejected', 1)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_approved',0)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
    
            }else{
                $items = LineItem::where('is_rejected', 0)->whereNotNull('buylist_id')->where('is_buylist',1)->where('is_approved',0)->select([
                    'id', 'is_disputed', 'is_hazmat', 'created_at', 'source_url', 'supplier', 
                    'name', 'order_note', 'asin', 'unit_purchased', 'buy_cost', 'product_buyer_notes','is_rejected','rejection_reason','is_approved'
                ]);
            }
        }
        if($request->start_date != null && $request->end_date != null){
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $items->whereBetween('created_at', [$startDate, $endDate]);
        }
        $is_rejected = $request->is_rejected;
        
            // Get the count of rejected items
        $rejectedCount = LineItem::where('is_rejected', true)->count();
        if (!$request->has('order') || empty($request->order)) {
            $items->orderBy('created_at', 'desc');
        }
     
        return DataTables::of($items)
            //  ->editColumn('actions', function($order) use ($request) {
            //     $undoRejectButton = '<button class="btn btn-sm btn-success undoRejectItem py-0 px-1" data-id="' . $order->id . '">
            //         <i class="ri-arrow-go-back-line"></i> Undo Rejected
            //     </button>';

            //     if($request->has('is_approved') && $request->is_approved != 'false'){
            //         $isAprroved = '';
            //     } else {
            //         $isAprroved = '<button class="btn btn-sm btn-success approvelItem py-0 px-1" data-id="'.$order->id.'" data-viewonly="true">
            //             <i class="ri-check-fill"></i> Approve
            //         </button>';
            //     }

            //     $rejectButton = '<button class="btn btn-sm btn-danger rejectItem py-0 px-1" data-id="' . $order->id . '">
            //         <i class="ri-forbid-2-line"></i> Reject
            //     </button>';

            //     $deleteButton = '<button class="btn btn-sm btn-danger deleteItem py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-delete-bin-line"></i> Delete
            //     </button>';

            //     $editButton = '<button class="btn btn-sm btn-info editItem py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-pencil-fill"></i> Edit
            //     </button>';

            //     $duplicateButton = '<button class="btn btn-sm btn-info duplicateItem py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-file-copy-line"></i> Duplicate
            //     </button>';

            //     $moveCopyButton = '<button class="btn btn-sm btn-secondary moveCopy py-0 px-1" data-id="'.$order->id.'">
            //         <i class="ri-share-forward-fill"></i> Move/Copy
            //     </button>';

            //     $orderButton = '<button class="btn btn-primary btn-block w-100 mt-1 singleOrder" data-id="'.$order->id.'">
            //         <i class="ri-shopping-cart-2-line"></i> Create Single Item Order
            //     </button>';

            //     return '
            //         <div class="d-flex flex-column align-items-start" style="max-width: auto;">
            //         <input type="checkbox" name="leadCheckBox" class="item-checkbox me-1" onchange="singleChecked('.$order->id.')" value="'.$order->id.'">
            //             <div class="d-flex flex-wrap gap-1 mb-1">
            //                 '.$isAprroved.'
            //                 '.$editButton.'
            //                 '.($order->is_rejected ? $undoRejectButton : $rejectButton).'
                            
            //                 '.$deleteButton.'
            //                 '.$duplicateButton.'
            //                 '.$moveCopyButton.'
            //             </div>
            //             '.$orderButton.'
            //         </div>
            //     ';
            // })
            ->addColumn('checkbox', function($order) {
                return '<input type="checkbox" class="item-checkbox" onchange="singleChecked('.$order->id.')" value="'.$order->id.'">';
            })
            ->editColumn('rejection_reason', function($order) {
                return  $order->rejection_reason;
            })
            ->editColumn('flags', function($order) {
                $outPut = '';
                if($order->is_hazmat)
                $outPut .= '<i class="ri-alarm-warning-fill text-danger ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Hazmat item"></i>';
                if($order->is_disputed)
                $outPut .=' <i class="ri-haze-2-line text-warning ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Item data may be disputed"></i>';
                return $outPut;
            })
            ->editColumn('created_at', function($order) {
                return  Carbon::parse($order->created_at) ->format('M d, Y');
            })
            ->editColumn('source_url', function($order) {
                return '<a href="' . $order->source_url . '" target="_blank">' . $order->supplier . '</a>';
            })
            ->editColumn('name', function($order) {
                $fullName = $order->name ?? '';
                $truncated = strlen($fullName) > 40 ? substr($fullName, 0, 40) . '...' : $fullName;

                return '<a href="#" title="'.e($fullName).'" data-bs-toggle="tooltip" data-bs-placement="top">'
                        . e($truncated) .
                    '</a>';
            })
            ->editColumn('order_note', function($order) {
                return $order->order_note ;
            })
            ->editColumn('asin', function($order) {
                return '<a href="https://www.amazon.com/dp/'.$order->asin.'" target="_blank">'.$order->asin.'</a>';
            })
            ->editColumn('unit_purchased', function($order) {
                return $order->unit_purchased ;
            })
            ->editColumn('buy_cost', function($order) {
                return  '$'.$order->buy_cost ;
            })
            ->addColumn('approve', function($order) use ($request) {
                if ($request->has('is_approved') && $request->is_approved != 'false') {
                    return '';
                }
                return '<button class="btn btn-sm btn-success approvelItem" data-id="'.$order->id.'">
                            <i class="ri-check-fill"></i>
                        </button>';
            })
            ->addColumn('edit', fn($order) =>
                '<button class="btn btn-sm btn-info editItem" data-id="'.$order->id.'">
                    <i class="ri-pencil-fill"></i>
                </button>'
            )
            ->addColumn('reject', fn($order) =>
                $order->is_rejected
                    ? '<button class="btn btn-sm btn-success undoRejectItem" data-id="'.$order->id.'">
                        <i class="ri-arrow-go-back-line"></i>
                    </button>'
                    : '<button class="btn btn-sm btn-danger rejectItem" data-id="'.$order->id.'">
                        <i class="ri-forbid-2-line"></i>
                    </button>'
            )
            ->addColumn('delete', fn($order) =>
                '<button class="btn btn-sm btn-danger deleteItem" data-id="'.$order->id.'">
                    <i class="ri-delete-bin-line"></i>
                </button>'
            )
            ->addColumn('duplicate', fn($order) =>
                '<button class="btn btn-sm btn-info duplicateItem" data-id="'.$order->id.'">
                    <i class="ri-file-copy-line"></i>
                </button>'
            )
            ->addColumn('move_copy', fn($order) =>
                '<button class="btn btn-sm btn-secondary moveCopy" data-id="'.$order->id.'">
                    <i class="ri-share-forward-fill"></i>
                </button>'
            )
            ->addColumn('order', fn($order) =>
                '<button class="btn btn-sm btn-primary singleOrder" data-id="'.$order->id.'">
                    <i class="ri-shopping-cart-2-line"></i>
                </button>'
            )
            ->with('rejectedCount', $rejectedCount)
            ->rawColumns(['checkbox','approve','edit','reject','delete','duplicate','move_copy','order', 'rejection_reason','created_at','source_url','name','order_note','asin','unit_purchased','buy_cost'])
            ->make(true);
    }

    public function remnameBuyList(Request $request){
        $find = Buylist::where('id',$request->id)->first();
        if($find){
            $buylist = $find->update([
                'name' => $request->name
            ]);
            return response()->json(['success' => true, 'message' => 'Buylist updated successfully.']);
        }
    }
    public function deleteBuyList(Request $request){
        $find = Buylist::where('id',$request->id)->first();
        if($find){
            LineItem::where('buylist_id', $find->id)
            ->update(['buylist_id' => 1]);
            $buylist = $find->delete();
            return response()->json(['success' => true, 'message' => 'Buylist deleted successfully.']);
        }
    }
    public function saveData(Request $request){
        $data = $request->all();
        $data['is_buylist']=1;
        $data['is_hazmat']= $data['is_hazmat']=='true'?1:0;
        $data['is_disputed']= $data['is_disputed']=='true'?1:0;
        $data['min']= $data['min']??0;
        $data['max']= $data['max']??0;
        $data['list_price']= $data['list_price']??0;
        if(auth()->user() != null){
            $data['created_by']= auth()->user()->id;
        }
        $newData = New LineItem;
       $newCreated= $newData->create($data);
        if($newCreated){
            return response()->json(['success' => true, 'message' => 'Buylist Lead successfully.']);
        }else{
            return response()->json(['success' => true, 'message' => 'Buylist Lead successfully.']);   
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
    public function deleteItem($id){
        $find = LineItem::find($id);
        $totalItems = 0;
        if($find->order_id != null){
            $findOrder = Order::where('id',$find->order_id)->first();
            if( $findOrder){
                $findOrder->total_units_purchased = $findOrder->total_units_purchased - $find->total_units_purchased;
                $findOrder->save();
                $totalItems = $findOrder->total_units_purchased;
            }
        }
        if($find->delete()){
            return response()->json(['success' => true, 'message' => 'Buylist Lead Deleted successfully.','total_items'=>$totalItems]);
        }else{
            return response()->json(['success' => true, 'message' => 'Buylist Lead Deleted successfully.']);   
        }
    }
    public function rejectItem($id,Request $request){
        $find = LineItem::find($id);
        if($find){
            $find->is_rejected = 1;
            $find->rejection_reason = $request->reason;
            $find->save();
            $totalItems = 0;
            if($find->order_id != null){
                $findOrder = Order::where('id',$find->order_id)->first();
                if( $findOrder){
                    $findOrder->total_units_purchased = $findOrder->total_units_purchased - $find->total_units_purchased;
                    $findOrder->save();
                    $totalItems = $findOrder->total_units_purchased;
                }
            }
            return response()->json(['success' => true, 'message' => 'Buylist Lead Rejected successfully.','total_items'=>$totalItems]);
        }else{
            return response()->json(['success' => false, 'message' => 'Buylist Lead Deleted successfully.']);   
        }
    }
    public function undoRejection($id){
        $find = LineItem::find($id);
        if($find){
            $find->is_rejected = 0;
            $find->rejection_reason = '';
            $find->save();
            return response()->json(['success' => true, 'message' => 'Buylist Lead UndoRejected successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Buylist Lead Deleted successfully.']);   
        }
    }
    public function duplicateItem(Request $request, $id){
        // Find the existing item
        $buylist = LineItem::findOrFail($id);
        $totalItems = 0;
        if($buylist->order_id != null){
            $findOrder = Order::where('id',$buylist->order_id)->first();
            if( $findOrder){
                $findOrder->total_units_purchased = $findOrder->total_units_purchased + $buylist->total_units_purchased;
                $findOrder->save();
                $totalItems = $findOrder->total_units_purchased;
            }
        }
        // Duplicate the item
        $newBuylist = $buylist->replicate(); // Create a copy of the item
        // Modify any fields as necessary
        $newBuylist->created_at = now(); // Set a new creation date if needed
        $newBuylist->save();
        return response()->json([
            'success' => true,
            'message' => 'Item duplicated successfully!',
            'data' => $newBuylist, // Return the duplicated item if needed
            'total_items' => $totalItems, // Return the duplicated item if needed
        ]);
    }
    public function editItem( $id){
        // Find the existing item
        $buylist = LineItem::with(['createdBy'])->findOrFail($id);
        return response()->json($buylist);
    }
    public function updateItemData($id,Request $request){
        // Find the existing item
        $data = $request->all();
       if(isset($data['modalMode']) && $data['modalMode'] == 'edit'){
            unset($data['modalMode']);
            $buylist = LineItem::findOrFail($id);
            if($buylist->update($data)){
                return response()->json([
                    'success' => true,
                    'message' => 'Item updated successfully!',
                ]);

            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'something wrong!',
                ]);
            }
        }else if(isset($data['modalMode']) && $data['modalMode'] == 'reject'){
            $buylist = LineItem::findOrFail($id);
            if($buylist){
                $buylist->update([
                    'is_rejected'=>1
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Item Rejected Successfully!',
                ]);

            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'something wrong!',
                ]);
            }
           
        }else{
            $buylist = LineItem::findOrFail($id);
            if($buylist){
                $buylist->update([
                    'is_approved'=>1
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Item Approved Successfully!',
                ]);

            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'something wrong!',
                ]);
            }
        }
        
    }
    public function moveOrCopyItem($itemId, Request $request){
        $item = LineItem::findOrFail($itemId);
        $targetBuylistId = $request->input('buylist_id');
        $isCopy = $request->input('is_copy');
        if ($isCopy) {
            // Copy the item to the new buylist
            $newItem = $item->replicate();
            $newItem->buylist_id = $targetBuylistId;
            $newItem->save();
            $message = 'Item successfully copied to the selected buylist.';
        } else {
            // Move the item to the new buylist
            $item->buylist_id = $targetBuylistId;
            $item->save();
            $message = 'Item successfully moved to the selected buylist.';
        }
        return response()->json(['message' => $message]);
    }
    public function createSingleOrder($itemId){
        // Find the line item by ID
        $lineItem = LineItem::find($itemId);
        // dd( $lineItem );

        if ($lineItem) {
            // Create a new order and save it
            $total_sku = $lineItem ->unit_purchased * $lineItem->buy_cost;
            $order = Order::create([
                'date' => now(),
                'status' => 'draft', // Set a default status
                'total_units_purchased'  => $lineItem->unit_purchased,
                'source'  => $lineItem->source_url,
                'is_pending'  => 1
                // Add any other necessary order fields here
            ]);

            // Update line item with the new order ID, set is_buylist to 1, and nullify buylist_id
            $lineItem->update([
                // 'is_buylist' => 0,
                // 'buylist_id' => null,
                'order_id' => $order->id,
                'sku_total' => $total_sku,
            ]);
            return response()->json(['success' => true, 'message' => 'Order created and item moved successfully.','order_id'=>$order->id,'buylist_id'=>$lineItem->buylist_id]);
        } else {
            return response()->json(['success' => false, 'message' => 'Item not found.']);
        }
    }
    public function deleteMultipleItems(Request $request){
        $itemIds = $request->input('ids', []);
        LineItem::whereIn('id', $itemIds)->delete();
        return response()->json(['success' => true, 'message' => 'Items deleted successfully.']);
    }
    public function copyMoveMultiple(Request $request){
        $itemIds = $request->input('ids', []);
        $buylistId = $request->input('buylist_id');
        foreach ($itemIds as $id) {
            $copy = $request->copy;
            $item = LineItem::find($id);
            if ($copy == 1) {
                $newItem = $item->replicate();
                $newItem->buylist_id = $buylistId;
                $newItem->save();
            } else {
                // Move the item to the selected buylist
                $item->buylist_id = $buylistId;
                $item->save();
            }
        }
        return response()->json(['success' => true, 'message' => $copy ? 'Items copied successfully.' : 'Items moved successfully.']);
    }
    public function createMultipleItemsOrder(Request $request){
        $itemIds = $request->input('ids', []);
        $order_id = $this->createOrderForItem();
        $units = 0;
        foreach ($itemIds as $id) {
            $item = LineItem::find($id);
            $total_sku = $item ->unit_purchased * $item->buy_cost;
            $item->is_buylist = 0;
            $item->buylist_id = null;
            $item->sku_total = $total_sku;
            $item->order_id = $order_id;
            $item->save();
            $units += $item->unit_purchased;
        }
        $findOrder = Order::where('id',$order_id)->first();
        $findOrder->total_units_purchased  = $units;
        $findOrder->total = $request->buy_cost * $request->unit_purchased;
        $findOrder->subtotal = $request->buy_cost * $request->unit_purchased;
        $findOrder->save();
        return response()->json(['success' => true, 'message' => 'Orders created successfully.','order_id'=>$order_id]);
    }
    // Helper method to create an order (you can customize this logic)
    private function createOrderForItem(){
        $order = Order::create([
            'date' => now(),
            'status' => 'ordered', // Set a default status
            // Add any other necessary order fields here
        ]);
        return $order->id;
    }
    public function saveBuyListBundleItems(Request $request){
        $orderItems = new LineItem;
        $orderItems->name = $request->name;
        $orderItems->buylist_id = $request->buylist_id;
        $orderItems->is_buylist = 1;
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
        if(auth()->user() != null){
            $orderItems->created_by = auth()->user()->id;
        }
        $orderItems->save();
        if($request->bundles != null){
            foreach($request->bundles as $bundle){
                $newbundle = new BundleItem();
                $newbundle->item_id = $orderItems->id;
                $newbundle->name = $bundle['name'];
                $newbundle->supplier = $bundle['supplier'];
                $newbundle->source_url = $bundle['url'];
                $newbundle->promo = $bundle['promo'];
                $newbundle->cost = $bundle['cost'];
                $newbundle->coupon_code = $bundle['coupon'];
                $newbundle->is_buylist = 1;
                $newbundle->save();
            }
        }
        return response()->json([
            'success'=>true,
        ]);
    }
}
