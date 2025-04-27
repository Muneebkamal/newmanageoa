<?php

namespace App\Http\Controllers;

use App\Models\EventLog;
use App\Models\LineItem;
use App\Models\Order;
use App\Models\ShipEvent;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('view_shipping')) {
            abort(403);
        }
        $statusCounts = Shipping::selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

    // Prepare counts for your dropdown
    $counts = [
        'all' => Shipping::count(),
        'open' => $statusCounts['open'] ?? 0,
        'pending' => $statusCounts['pending'] ?? 0,
        'in_transit' => $statusCounts['in_transit'] ?? 0,
        'closed' => $statusCounts['close'] ?? 0,
    ];
        return view('shipping.index',get_defined_vars());
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
       $data = $request->all();
       $new = new Shipping;
       $new->create($data);
       if($new){
        $response['message'] = 'Shipping added Successfully!';
        $response['status_cdoe'] = 200;
        $response['success'] = true;
       }else{
        $response['message'] = 'Something Wrong!';
        $response['status_cdoe'] = 400;
        $response['success'] = false;
       }
       return  response()->json($response);
    }
    public function getShippingBatches(Request $request){
        if ($request->ajax()) {
            $query =  Shipping::select(['id','status', 'name', 'market_place', 'date', 'tracking_number', 'items']);

            // Apply filtering based on status
            if ($request->status && $request->status !== 'all') {
                $query->where('status', ucfirst($request->status)); // Adjust case sensitivity
            }
            return DataTables::of($query)
                 // Format Status Column
            ->editColumn('status', function ($row) {
                // Map the status to appropriate classes and display text
                $statusClass = '';
                $displayText = '';
                switch ($row->status) {
                    case 'open':
                        $statusClass = 'bg-primary'; // Blue for Open
                        $displayText = 'Open';
                        break;
                    case 'pending':
                        $statusClass = 'bg-warning'; // Yellow for Pending
                        $displayText = 'Pending';
                        break;
                    case 'close':
                        $statusClass = 'bg-dark'; // Dark for Close
                        $displayText = 'Close';
                        break;
                    case 'in_transit':
                        $statusClass = 'bg-success'; // Green for In Transit
                        $displayText = 'In Transit';
                        break;
                    default:
                        $statusClass = 'bg-secondary'; // Grey for unknown statuses
                        $displayText = 'Unknown';
                        break;
                }
                return '<span class="badge ' . $statusClass . '">' . ucfirst( $displayText) . '</span>';
            })
            // Format Name Column
            ->editColumn('name', function ($row) {
                return $row->name;
            })
            // Format Marketplace Column
            ->editColumn('market_place', function ($row) {
                return strtoupper($row->market_place);
            })
            // Format Shipped Date Column
            ->editColumn('date', function ($row) {
                return \Carbon\Carbon::parse($row->date)->format('M d, Y');
            })
            // Format Tracking Number Column
            ->editColumn('tracking_number', function ($row) {
                return $row->tracking_number;
            })
            // Format Quantity Column
            ->editColumn('items', function ($row) {
                return $row->quantity;
            })
            // Actions Column with HTML for Buttons
            ->editColumn('actions', function ($row) {
                return ' <a href="'.url("shippingbatch/$row->id").'" class="btn btn-outline-info btn-sm">
                            <i class=" ri-folder-open-line"></i> View
                        </a>
                <a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-outline-info btn-sm btn-edit" onclick="editShipping('.$row->id.')">
                            <i class=" ri-file-edit-fill"></i> Edit
                        </a>
                        <button data-id="' . $row->id . '" class="btn btn-outline-danger btn-sm btn-delete" onclick="deleteShipping('.$row->id.')">
                            <i class="ri-delete-bin-5-fill"></i> Delete
                        </button>';
            })
            ->rawColumns(['status', 'actions'])  // Specify raw HTML columns
            ->make(true);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!\Auth::user()->can('view_shipping')) {
            abort(403);
        }
        $shipping = Shipping::find($id);
        return view('shipping.view-shipping',get_defined_vars());
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Show data for editing
    public function edit($id)
    {
        $shippingBatch = Shipping::findOrFail($id);
        return response()->json($shippingBatch);
    }

    // Update the record
    public function update(Request $request, $id)
    {
        
        $shippingBatch = Shipping::findOrFail($id);
        $shippingBatch->update($request->all());

        return response()->json(['message' => 'Record updated successfully.']);
    }

    // Delete the record
    public function destroy($id)
    {
        $shippingBatch = Shipping::findOrFail($id);
        $shippingBatch->delete();

        return response()->json(['message' => 'Record deleted successfully.']);
    }
    public function getShipping(){
        $data = Shipping::where('status','Open')->get();
        return response()->json($data);
    }
    public function saveShippingEvent(Request $request){
       $data = $request->all();
       $data['image_matches_flag'] = $request->boolean('image_matches_flag') ? 1 : 0;
       $data['description_matches_flag'] = $request->boolean('description_matches_flag') ? 1 : 0;
       $data['upc_matches_flag'] = $request->boolean('upc_matches_flag') ? 1 : 0;
       $data['title_matches_flag'] = $request->boolean('title_matches_flag') ? 1 : 0;
       $newShippingEvent = new ShipEvent;
       $newShippingEvent->create($data);
       $findItem =  LineItem::where('id',$data['order_item_id'])->first();
        if ($findItem && isset($data['items'])) {
           // Increment the unit_error by adding the new item_quantity
           $findItem->total_units_received += $data['items'];
           $findItem->total_units_shipped += $data['items'];
           $findItem->save(); // Save the updated value
        }
       $findOrder =  Order::where('id',$data['order_id'])->first();
       if ($findOrder && isset($data['items'])) {
           $findOrder->total_units_shipped += $data['items'];
           $findOrder->total_units_received += $data['items'];
           $findOrder->save(); // Save the updated value
       }
        return response()->json([ 
            'success'=>true,
            'message' => 'Event addedsuccessfully.',
            'total_received_items'=> $findItem->total_units_received ,
            'total_ship_items'=> $findItem->total_units_shipped ,
            'total_ship_order'=> $findOrder->total_units_shipped ,
            'total_received_order'=> $findOrder->total_units_received,
            'order_item_id'=> $findItem->id,
        ]);

    }
    public function deleteShipping($id){
        
        $evetns = ShipEvent::where('id',$id)->first();
        $response = array();
        $findItem =  LineItem::where('id',$evetns->order_item_id)->first();
        if($evetns){
            if ($findItem ) {
                // Increment the unit_error by adding the new item_quantity
                $findItem->total_units_received -= $evetns->item_quantity;
                $findItem->total_units_shipped -= $evetns->item_quantity;
                $findItem->save(); // Save the updated value
            }
            $findOrder =  Order::where('id',$evetns->order_id)->first();
            if ($findOrder) {
                $findOrder->total_units_received -= $evetns->item_quantity;
                $findOrder->total_units_shipped -= $evetns->item_quantity;
                $findOrder->save(); // Save the updated value
            }
            $evetns->delete();
            $response =[
                'message'=>'The Shipping event has been removed.',
                'success'=>true,
                'status_code'=>200,
                'total_received_items'=> $findItem->total_units_received ,
                'total_ship_items'=> $findItem->total_units_shipped ,
                'total_ship_order'=> $findOrder->total_units_shipped ,
                'total_received_order'=> $findOrder->total_units_received
            ];
        }
        return response()->json($response);
    }
    public function getShippingEvetsAll($id){
        $evetns = ShipEvent::where('shipping_batch',$id)->with(['order','orderItem'])->get();
        return response()->json($evetns);
    }
    public function getSingleEvent($id,Request $request){
        if($request->type == 'ship'){
            $event = ShipEvent::where('id',$id)->first();
        }else{
            $event = EventLog::where('id',$id)->first();
        }
        return response()->json($event);
    }
    public function updateshippingEvent(Request $request){
        $data = $request->all();
        $finEvent = ShipEvent::where('id',$request->ship_event_id)->first();
        if($finEvent){
            $findItem =  LineItem::where('id',$finEvent->order_item_id)->first();
            if ($findItem) {
                $findItem->total_units_received = $findItem->total_units_received - $finEvent->items;
                $findItem->total_units_shipped = $findItem->total_units_shipped  - $finEvent->items;
                $findItem->total_units_received = $findItem->total_units_received + $request->items;
                $findItem->total_units_shipped = $findItem->total_units_shipped + $request->items;
                $findItem->save(); // Save the updated value
            }
            $findOrder =  Order::where('id',$finEvent->order_id)->first();
            if ($findOrder) {
                $findOrder->total_units_shipped  =  $findOrder->total_units_shipped - $finEvent->items;
                $findOrder->total_units_received = $findOrder->total_units_received- $finEvent->items;
                $findOrder->total_units_shipped  =  $findOrder->total_units_shipped + $request->items;
                $findOrder->total_units_received = $findOrder->total_units_received + $request->items;
                $findOrder->save(); // Save the updated value
            }
        
            $finEvent->update($data);
            return response()->json([ 
                'success'=>true,
                'message' => 'Event updated successfully.',
                'total_received_items'=> $findItem->total_units_received ,
                'total_ship_items'=> $findItem->total_units_shipped ,
                'total_ship_order'=> $findOrder->total_units_shipped ,
                'total_received_order'=> $findOrder->total_units_received,
                'order_item_id'=> $findItem->id,
            ]);
        }
    }
    public function updateIssueEvent(Request $request,$id){
        $data = $request->all();
        $data['received'] = $request->input('received') ? 1 : 0;
        $data['cc_charged'] = $request->input('cc_charged') ? 1 : 0;
        $data['cancelled'] = $request->input('cancelled') ? 1 : 0;
        $data['refunded'] = $request->input('refunded') ? 1 : 0;
        $finEvent = EventLog::where('id',$id)->first();
        if($finEvent){
            $findItem =  LineItem::where('id',$finEvent->order_item_id)->first();
            if ($findItem) {
                $findItem->unit_errors = $findItem->unit_errors - $finEvent->item_quantity;
                $findItem->unit_errors = $findItem->unit_errors + $request->item_quantity;
                $findItem->save(); // Save the updated value
            }
            $findOrder =  Order::where('id',$finEvent->order_id)->first();
            if ($findOrder) {
                $findOrder->unit_errors = $findOrder->unit_errors - $finEvent->item_quantity;
                $findOrder->unit_errors = $findOrder->unit_errors + $request->item_quantity;
                $findOrder->save(); // Save the updated value
            }
            $finEvent->update($data);
            return response()->json([ 
                'success'=>true,
                'message' => 'Event updated successfully.',
                'item_total_error'=> $findItem->unit_errors,
                'order_total_error'=> $findOrder->unit_errors,
                'order_id'=> $findOrder->id,
                'order_item_id'=> $findItem->id,
            ]);
        }
    }
}
