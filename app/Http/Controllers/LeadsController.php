<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Lead;
use App\Models\LineItem;
use App\Services\KeepaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

    class LeadsController extends Controller
    {
        protected $keepaService;

        public function __construct(KeepaService $keepaService)
        {
            $this->keepaService = $keepaService;
        }
        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            // Check if the user has permission to edit this specific employee
            if (!\Auth::user()->can('view_leads')) {
                abort(403);
            }
            $sku = LineItem::where('is_buylist',1)->where('is_rejected',0)->count();
            $cost = LineItem::where('is_buylist',1)->where('is_rejected',0)->sum('buy_cost');
            $units = LineItem::where('is_buylist',1)->where('is_rejected',0)->sum('unit_purchased');
            $leads = Lead::latest('created_at')->paginate(5);
            return view('leads.index', get_defined_vars());
        }
        public function index2()
        {
            // Check if the user has permission to edit this specific employee
            if (!\Auth::user()->can('view_leads')) {
                abort(403);
            }
            $sku = LineItem::where('is_buylist',1)->where('is_rejected',0)->count();
            $cost = LineItem::where('is_buylist',1)->where('is_rejected',0)->sum('buy_cost');
            $units = LineItem::where('is_buylist',1)->where('is_rejected',0)->sum('unit_purchased');
            $leads = Lead::latest('created_at')->paginate(5);
            return view('leads.index2', get_defined_vars());
        }
        public function getLeadsData(Request $request)
        {
            $selectedIds = $request->input('selected_soruce_ids', []);
            if(auth()->user()->role_id == 1){
                if (!empty($selectedIds)) {
                    $leads = Lead::whereIn('source_id', $selectedIds)->with('source')->where('is_rejected',0);
                }else{
                    $leads = Lead::with('source')->where('is_rejected',0);
                }
            }else{
                $leads = Lead::with('source')->where('created_by', auth()->user()->id)->where('is_rejected',0);
                // Apply filtering based on selected IDs
                if (!empty($selectedIds)) {
                    $leads->whereIn('source_id', $selectedIds);
                }
            }
            if (request()->has('user_id') && !empty(request('user_id'))) {
                $userId = request('user_id');
                $leads->where('created_by', $userId);
            }
    
            if ($request->has('start_date') && $request->has('end_date') && !empty($request->start_date) && !empty($request->end_date)) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $leads->whereBetween('created_at', [$startDate,  $endDate]);
            }

            // Sort By
            if ($request->has('sort_by_new') && $request->has('order_in') && $request->input('sort_by_new') !== null && $request->input('order_in') !== null) {

                $leads->orderBy($request->input('sort_by_new'), $request->input('order_in'));
            }

            // ROI Filter
            if ($request->has(['roi_min', 'roi_max']) && $request->input('roi_min') !== null && $request->input('roi_max') !== null) {

                $roiMin = $request->input('roi_min');
                $roiMax = $request->input('roi_max');
                $leads->whereBetween('roi', [$roiMin, $roiMax]);
            }

            // Net Profit Filter
            if ($request->has(['net_profit_min', 'net_profit_max']) && $request->input('net_profit_min') !== null && $request->input('net_profit_max') !== null) {
                $netProfitMin = $request->input('net_profit_min');
                $netProfitMax = $request->input('net_profit_max');

                $leads->whereBetween('net_profit', [$netProfitMin, $netProfitMax]);
            }

            // Low FBA Filter
            if ($request->has(['low_fba_min', 'low_fba_max']) && $request->input('low_fba_min') !== null && $request->input('low_fba_max') !== null) {
                $lowFbaMin = $request->input('low_fba_min');
                $lowFbaMax = $request->input('low_fba_max');

                $leads->whereBetween('sell_price', [$lowFbaMin, $lowFbaMax]);
            }
            
            if ($request->has('checkedValueTags')) {
                $checkValuesTags = $request->input('checkedValueTags');
                if (in_array(0, $checkValuesTags)) {
                    $leads->whereNull('tags');
                } else {
                    $leads->whereIn('tags', $checkValuesTags);
                }
            }

            // 90 Day Avg Filter
            if ($request->has(['ninety_day_avg_min', 'ninety_day_avg_max']) && $request->input('ninety_day_avg_min') !== null && $request->input('ninety_day_avg_max') !== null) {
                $ninetyDayAvgMin = $request->input('ninety_day_avg_min');
                $ninetyDayAvgMax = $request->input('ninety_day_avg_max');

                $leads->whereBetween('bsr', [$ninetyDayAvgMin, $ninetyDayAvgMax]);
            }
            

            // Apply global search for DataTables
            if (request()->has('search') && !empty(request('search')['value'])) {
                $searchTerm = request('search')['value'];
        
                $leads->where(function ($query) use ($searchTerm) {
                    $query->where('source_id', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%")
                    ->orWhere('asin', 'like', "%{$searchTerm}%")
                    ->orWhere('supplier', 'like', "%{$searchTerm}%")
                    ->orWhere('category', 'like', "%{$searchTerm}%")
                    ->orWhere('notes', 'like', "%{$searchTerm}%")
                    ->orWhere('promo', 'like', "%{$searchTerm}%")
                    ->orWhere('coupon', 'like', "%{$searchTerm}%")
                    ->orWhereRaw("DATE_FORMAT(date, '%b %d, %Y') like ?", ["%{$searchTerm}%"]);
                });
            }
            $clonedLeads = clone $leads;
            // Remove any orderBy/limit clauses
            $clonedLeads->getQuery()->orders = null;
            $clonedLeads->getQuery()->limit = null;
            
            $stats = $clonedLeads->selectRaw('
                MIN(CAST(roi AS UNSIGNED)) as roi_min,
                MAX(CAST(roi AS UNSIGNED)) as roi_max,
                MIN(CAST(bsr AS UNSIGNED)) as bsr_min,
                MAX(CAST(bsr AS UNSIGNED)) as bsr_max,
                MIN(sell_price) as fba_min,
                MAX(sell_price) as fba_max,
                MIN(net_profit) as net_profit_min,
                MAX(net_profit) as net_profit_max
            ')->first();
            
            $roiMin = $stats->roi_min;
            $roiMax = $stats->roi_max;
            $bsrMin = $stats->bsr_min;
            $bsrMax = $stats->bsr_max;
            $fbaMin = $stats->fba_min;
            $fbaMax = $stats->fba_max;
            $netProfitMin = $stats->net_profit_min;
            $netProfitMax = $stats->net_profit_max;
            return DataTables::of($leads)
            ->editColumn('date', function ($lead) {
                return $lead->date; // Format date
            })
            ->with([
                'total_leads' => $leads->count(),
                'roi_min' => $roiMin,
                'roi_max' => $roiMax,
                'bsr_min' => $bsrMin,
                'bsr_max' => $bsrMax,
                'fba_min' => $fbaMin,
                'fba_max' => $fbaMax,
                'net_profit_min' => $netProfitMin,
                'net_profit_max' => $netProfitMax,
            ])
            ->make(true);
            return view('leads.index', compact('leads'));
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
            // dd($request->all());
            $source_id = $request->source_id;
            $request->validate([
                'source_id' => ['required'],
                'name' => ['required'],
                'asin' => ['required'],
                'cost' => ['required'],
                'sell_price' => ['required'],
                // 'category' => ['required'],
                'url' => ['required'],
            ]);
            
            $lead = new Lead;
            if(isset($request->bundle)){
                $lead->bundle = 1;
            }else{
                $lead->bundle = 0;
            }
            $lead->source_id = $request->source_id;
            $lead->date = $request->date;
            $lead->name = $request->name;
            $lead->asin = $request->asin;
            $lead->url = $request->url;
            $lead->supplier = $request->supplier;
            $lead->cost = $request->cost;
            $lead->sell_price = $request->sell_price;
            $lead->net_profit = $request->net_profit;
            $lead->roi = $request->item_roi;
            $lead->bsr = $request->item_bsr;
            $lead->category = $request->category;
            $lead->promo = $request->promo;
            $lead->notes = $request->notes;
            $lead->currency = $request->currency;
            $lead->coupon = $request->coupon;
            if(auth()->user()){
                $lead->created_by = auth()->user()->id;
            }
            if(isset($request->is_hazmat)){
                $lead->is_hazmat = 1;
            }else{
                $lead->is_hazmat = 0;
            }
            if(isset($request->is_disputed)){
                $lead->is_disputed = 1;
            }else{
                $lead->is_disputed = 0;
            }
            $lead->save();

            $lead_id = $lead->id;
            if($lead->bundle == 1){
                // Validate incoming request
                $request->validate([
                    'item' => 'required|array',
                    'item.*.name' => 'required|string',
                    'item.*.supplier' => 'required|string',
                    'item.*.promo' => 'nullable|string',
                    'item.*.url' => 'required|string',
                    'item.*.coupon' => 'nullable|string',
                    'item.*.cost' => 'required|numeric',
                    'item.*.notes' => 'nullable|string',
                ]);

                // Loop through the indexed array and save each item
                foreach ($request->item as $item) {
                    Item::create([
                        'lead_id' => $lead_id,
                        'name' => $item['name'],
                        'supplier' => $item['supplier'],
                        'promo' => $item['promo'] ?? null,
                        'url' => $item['url'],
                        'coupon' => $item['coupon'] ?? null,
                        'cost' => $item['cost'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Lead added successfully!',
                'data' => $source_id
            ]);
        }

        /**
         * Display the specified resource.
         */
        // public function show(Request $request, string $id)
        // {
        //     if ($request->ajax()) {
        //         if(auth()->user()->role_id == 1){
        //             $data = Lead::where('source_id', $id)->select('id', 'name', 'asin', 'url', 'category', 'cost', 'sell_price', 'notes', 'date')->get();
        //         }else{
        //             $data = Lead::where('source_id', $id)->where('created_by', auth()->user()->id)->select('id', 'name', 'asin', 'url', 'category', 'cost', 'sell_price', 'notes', 'date')->get();
        //         }
            
                
        //         return datatables()->of($data)
        //             // Optionally add an index column
        //             // ->addIndexColumn()
        //             ->addColumn('action', function ($row) {
        //                 return '
        //                 <div class="d-flex">
        //                     <div class="form-check">
        //                         <input class="form-check-input fs-15" name="sourceLeadChckbox" type="checkbox" id="singleCheck_'.$row->id.'" value="'.$row->id.'" onclick="singleCheck('.$row->id.')">
        //                     </div>
        //                     <i class="mdi mdi-dots-vertical fs-5 ms-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
        //                     <div class="dropdown-menu">
        //                         <a class="dropdown-item" data-bs-toggle="modal"
        //                             data-bs-target="#exampleModalScrollable" onclick="leadFind('.$row->id.')">
        //                             <i class="ri-pencil-line text-primary me-2"></i>Update Lead
        //                         </a>
        //                         <a class="dropdown-item" onclick="leadDelete('.$row->id.')">
        //                             <i class="ri-delete-bin-line text-danger me-2"></i>Delete Lead
        //                         </a>
        //                     </div>
        //                 </div>';
        //             })
        //             ->editColumn('name', function ($row) {
        //                 return $row->name;
        //             })
        //             ->editColumn('asin', function ($row) {
        //                 return '<a href="https://www.amazon.com/dp/' . $row->asin . '" class="text-primary">' . $row->asin . '</a>';
        //             })
        //             ->editColumn('url', function ($row) {
        //                 return '<a href="' . $row->url . '" class="text-primary">' . $row->supplier . '</a>';
        //             })
        //             ->editColumn('category', function ($row) {
        //                 return $row->category;
        //             })
        //             ->editColumn('cost', function ($row) {
        //                 return $row->cost;
        //             })
        //             ->editColumn('sell_price', function ($row) {
        //                 return $row->sell_price;
        //             })
        //             ->editColumn('notes', function ($row) {
        //                 return $row->notes;
        //             })
        //             ->editColumn('date', function ($row) {
        //                 return $row->date;
        //             })
        //             ->rawColumns(['action', 'asin', 'url']) // Specify columns that should be treated as raw HTML
        //             ->make(true);
        //     }
            
        //     // return view('welcome');
        // }
        public function show(Request $request, string $id)
    {
        if ($request->ajax()) {
            $perPage = (int) $request->get('length', 10); // Number of items per page
            $start = (int) $request->get('start', 0); // Starting point for pagination
            $draw = (int) $request->get('draw', 1); // Draw counter for DataTables
            // Base query
            $query = Lead::where('source_id', $id);
            // Apply user-specific filter (non-admin users)
            if (auth()->user()->role_id != 1) {
                $query->where('created_by', auth()->user()->id);
            }
            if ($request->has('sortField') && $request->get('sortField')) {
                $sortField = $request->get('sortField');
                $sortOrder = $request->get('sortOrder', 'ASC');
                $query->orderBy($sortField, $sortOrder);
            }
            $totalRecords = Lead::where('source_id', $id)->count();
            $recordsFiltered = $query->count();
            $data = $query->offset($start)->limit($perPage)->get();
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data->map(function ($row) {
                    return [
                        'action' => '
                            <div class="d-flex">
                                <div class="form-check">
                                    <input class="form-check-input fs-15" name="sourceLeadChckbox" type="checkbox" id="singleCheck_' . $row->id . '" value="' . $row->id . '" onclick="singleCheck(' . $row->id . ')">
                                </div>
                                <a class="btn btn-outline-primary btn-sm ms-1" data-bs-toggle="modal"
                                        data-bs-target="#exampleModalScrollable" onclick="leadFind(' . $row->id . ')">
                                        <i class="ri-pencil-line text-primary me-2"></i>Update Lead
                                    </a>
                                    <a class="btn btn-outline-danger btn-sm ms-1" onclick="leadDelete(' . $row->id . ')">
                                        <i class="ri-delete-bin-line text-danger me-2"></i>Delete Lead
                                    </a>
                                
                            </div>',
                        'name' => $row->name,
                        'asin' => '<a href="https://www.amazon.com/dp/' . $row->asin . '" class="text-primary" target="_blank">' . $row->asin . '</a>',
                        'url' => '<a href="' . $row->url . '" class="text-primary" target="_blank">' . $row->supplier . '</a>',
                        'category' => $row->category,
                        'cost' => $row->cost,
                        'sell_price' => $row->sell_price,
                        'notes' => $row->notes,
                        'date' => $row->date,
                    ];
                }),
            ]);
        }
    }



        public function getLatestLeads(Request $request, string $id)
        {
            if ($request->ajax()) {
                
                if($request->batchId != null){
                    $leadIds = session()->get('lead_batch_' . $request->batchId, []);
                }
                if(sizeof($leadIds)>0){
                    $data = Lead::where('source_id', $id)->whereIn('id',$leadIds)->get();
                }else{
                    $data = Lead::where('source_id', $id)->get();
                }
            
                
                return datatables()->of($data)
                    // Optionally add an index column
                    // ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        return '
                        <div class="d-flex">
                            <div class="form-check">
                                <input class="form-check-input fs-15" name="sourceLeadChckbox" type="checkbox" id="singleCheck_'.$row->id.'" value="'.$row->id.'" onclick="singleCheck('.$row->id.')">
                            </div>
                            <i class="mdi mdi-dots-vertical fs-5 ms-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#exampleModalScrollable" onclick="leadFind('.$row->id.')">
                                    <i class="ri-pencil-line text-primary me-2"></i>Update Lead
                                </a>
                                <a class="dropdown-item" onclick="leadDelete('.$row->id.')">
                                    <i class="ri-delete-bin-line text-danger me-2"></i>Delete Lead
                                </a>
                            </div>
                        </div>';
                    })
                    ->editColumn('name', function ($row) {
                        return $row->name;
                    })
                    ->editColumn('asin', function ($row) {
                        return '<span class="text-primary">' . $row->asin . '</span>';
                    })
                    ->editColumn('url', function ($row) {
                        return '<a href="' . $row->url . '" class="text-primary">' . $row->url . '</a>';
                    })
                    ->editColumn('category', function ($row) {
                        return $row->category;
                    })
                    ->editColumn('cost', function ($row) {
                        return $row->cost;
                    })
                    ->editColumn('sell_price', function ($row) {
                        return $row->sell_price;
                    })
                    ->editColumn('notes', function ($row) {
                        return $row->notes;
                    })
                    ->editColumn('date', function ($row) {
                        return $row->date;
                    })
                    ->rawColumns(['action', 'asin', 'url']) // Specify columns that should be treated as raw HTML
                    ->make(true);
            }
            
            // return view('welcome');
        }

        /**
         * Show the form for editing the specified resource.
         */
        public function edit(string $id)
        {
            $lead = Lead::where('id', $id)->with('createdBy')->first();
            return response()->json([
                'status' => 'success',
                'data' => $lead
            ]);
        }

        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, string $id)
        {   
            $source_id = $request->update_source_id;

            $lead = Lead::where('id', $id)->first();
            if(isset($request->bundle)){
                $lead->bundle = 1;
            }else{
                $lead->bundle = 0;
            }
            $lead->source_id = $request->update_source_id;
            $lead->date = $request->date;
            $lead->name = $request->name;
            $lead->asin = $request->asin;
            $lead->url = $request->url;
            $lead->supplier = $request->supplier;
            $lead->cost = $request->cost;
            $lead->sell_price = $request->sell_price;
            $lead->net_profit = $request->net_profit;
            $lead->roi = $request->item_roi;
            $lead->bsr = $request->item_bsr;
            $lead->category = $request->category;
            $lead->promo = $request->promo;
            $lead->notes = $request->notes;
            $lead->currency = $request->currency;
            $lead->coupon = $request->coupon;
            if(isset($request->is_hazmat)){
                $lead->is_hazmat = 1;
            }else{
                $lead->is_hazmat = 0;
            }
            if(isset($request->is_disputed)){
                $lead->is_disputed = 1;
            }else{
                $lead->is_disputed = 0;
            }
            $lead->save();

            return response()->json([
                'message' => 'Lead update successfully!',
                'data' => $source_id
            ]);
        }

        /**
         * Remove the specified resource from storage.
         */
        public function destroy(string $id)
        {
            $lead = Lead::where('id', $id)->first();
            $source_id = $lead->source_id;

            $lead->delete();

            return response()->json([
                'message' => 'Source Deleted successfully!',
                'data' => $source_id
            ]);
        }
        public function updateLeadsSoruces(Request $request){
            // Validate the request input
            $request->validate([
                'ids' => 'required|array',
                'leadSource' => 'required|integer', // Assuming lead source is an integer
            ]);
            // Retrieve the selected row ids and new lead source
            $ids = $request->input('ids');
            $newLeadSource = $request->input('leadSource');
            // Update the lead source for the selected rows
            Lead::whereIn('id', $ids)->update(['source_id' => $newLeadSource]);
            return response()->json(['success' => true, 'message' => 'Lead source updated successfully']);
        }
        public function updateDate(Request $request)
        {
            // Validate the request input
            $request->validate([
                'ids' => 'required|array',
                'date' => 'required|date', // Date should be a valid date format
            ]);

            // Retrieve the selected row ids and the new date
            $ids = $request->input('ids');
            $newDate = $request->input('date');

            // Update the date for the selected rows
            Lead::whereIn('id', $ids)->update(['date' => $newDate]);

            return response()->json(['success' => true, 'message' => 'Date updated successfully']);
        }
        public function deleteRows(Request $request){
            // Validate that 'ids' is an array and contains values
            $request->validate([
                'ids' => 'required|array',
            ]);
            // Retrieve the selected row ids
            $ids = $request->input('ids');
            // Perform the delete operation
            Lead::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Rows deleted successfully']);
        }
        public function getLeadData(Request $request){
            $lead = Lead::where('id',$request->id)->first();  
            if($lead ){
                return response()->json($lead );
            } 
        }
        public function ListView(Request $request){
        return view('lists.index');
        }
        public function getTopLeads(Request $request)
        {
            $leads = Lead::with('source')->take(10);
            
        // Filter by Date Range
            if ($request->has('date_range') && $request->input('date_range') !== null) {
                $dateRange = $request->input('date_range');
                if ($dateRange === 'custom' && $request->has(['start_date', 'end_date']) && $request->input('start_date') !== null && $request->input('end_date') !== null) {
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    $leads->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($dateRange !== '827') { // 827 = All
                    $leads->where('created_at', '>=', now()->subDays($dateRange));
                }
            }

            // Sort By
            if ($request->has('sort_by_new') && $request->has('order_in') && $request->input('sort_by_new') !== null && $request->input('order_in') !== null) {

                $leads->orderBy($request->input('sort_by_new'), $request->input('order_in'));
            }

            // ROI Filter
            if ($request->has(['roi_min', 'roi_max']) && $request->input('roi_min') !== null && $request->input('roi_max') !== null) {

                $roiMin = $request->input('roi_min');
                $roiMax = $request->input('roi_max');
                $leads->whereBetween('roi', [$roiMin, $roiMax]);
            }

            // Net Profit Filter
            if ($request->has(['net_profit_min', 'net_profit_max']) && $request->input('net_profit_min') !== null && $request->input('net_profit_max') !== null) {
                $netProfitMin = $request->input('net_profit_min');
                $netProfitMax = $request->input('net_profit_max');

                $leads->whereBetween('net_profit', [$netProfitMin, $netProfitMax]);
            }

            // Low FBA Filter
            if ($request->has(['low_fba_min', 'low_fba_max']) && $request->input('low_fba_min') !== null && $request->input('low_fba_max') !== null) {
                $lowFbaMin = $request->input('low_fba_min');
                $lowFbaMax = $request->input('low_fba_max');

                $leads->whereBetween('sell_price', [$lowFbaMin, $lowFbaMax]);
            }

            // 90 Day Avg Filter
            if ($request->has(['ninety_day_avg_min', 'ninety_day_avg_max']) && $request->input('ninety_day_avg_min') !== null && $request->input('ninety_day_avg_max') !== null) {
                $ninetyDayAvgMin = $request->input('ninety_day_avg_min');
                $ninetyDayAvgMax = $request->input('ninety_day_avg_max');

                $leads->whereBetween('bsr', [$ninetyDayAvgMin, $ninetyDayAvgMax]);
            }

            // Apply global search for DataTables
            if (request()->has('search') && !empty(request('search')['value'])) {
                $searchTerm = request('search')['value'];
        
                $leads->where(function ($query) use ($searchTerm) {
                    $query->where('source_id', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%")
                    ->orWhere('asin', 'like', "%{$searchTerm}%")
                    ->orWhere('supplier', 'like', "%{$searchTerm}%")
                    ->orWhere('category', 'like', "%{$searchTerm}%")
                    ->orWhere('notes', 'like', "%{$searchTerm}%")
                    ->orWhere('promo', 'like', "%{$searchTerm}%")
                    ->orWhere('coupon', 'like', "%{$searchTerm}%")
                    ->orWhereRaw("DATE_FORMAT(date, '%b %d, %Y') like ?", ["%{$searchTerm}%"]);
                });
            }
            
            return DataTables::of($leads)
            ->editColumn('date', function ($lead) {
                return $lead->date; // Format date
            })
            ->make(true);
            return view('leads.index', compact('leads'));
        }
        public function updateBlukTags(Request $request){
            
            foreach( $request->leadIds as $lead ){
                $updateLead = Lead::where('id',$lead)->first();
                if ($updateLead) {
                    $existingTags = $updateLead->tags ? explode(',', $updateLead->tags) : [];
                    if (!in_array($request->tag, $existingTags)) {
                        $existingTags[] = $request->tag; // Append the new tag
                        $updateLead->tags = implode(',', $existingTags); // Convert back to a comma-separated string
                        $updateLead->save();
                    }
                }
            }
            return response()->json(['success' => true, 'message' => 'Rows deleted successfully']);
        }
        public function getTableViewData(Request $request)
        {
            $leads = Lead::with('source');
        // Filter by Date Range
            if ($request->has('date_range') && $request->input('date_range') !== null) {
                $dateRange = $request->input('date_range');
                if ($dateRange === 'custom' && $request->has(['start_date', 'end_date']) && $request->input('start_date') !== null && $request->input('end_date') !== null) {
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    $leads->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($dateRange !== '827') { // 827 = All
                    $leads->where('created_at', '>=', now()->subDays($dateRange));
                }
            }

            // Sort By
            if ($request->has('sort_by_new') && $request->has('order_in') && $request->input('sort_by_new') !== null && $request->input('order_in') !== null) {

                $leads->orderBy($request->input('sort_by_new'), $request->input('order_in'));
            }

            // ROI Filter
            if ($request->has(['roi_min', 'roi_max']) && $request->input('roi_min') !== null && $request->input('roi_max') !== null) {

                $roiMin = $request->input('roi_min');
                $roiMax = $request->input('roi_max');
                $leads->whereBetween('roi', [$roiMin, $roiMax]);
            }

            // Net Profit Filter
            if ($request->has(['net_profit_min', 'net_profit_max']) && $request->input('net_profit_min') !== null && $request->input('net_profit_max') !== null) {
                $netProfitMin = $request->input('net_profit_min');
                $netProfitMax = $request->input('net_profit_max');

                $leads->whereBetween('net_profit', [$netProfitMin, $netProfitMax]);
            }

            // Low FBA Filter
            if ($request->has(['low_fba_min', 'low_fba_max']) && $request->input('low_fba_min') !== null && $request->input('low_fba_max') !== null) {
                $lowFbaMin = $request->input('low_fba_min');
                $lowFbaMax = $request->input('low_fba_max');

                $leads->whereBetween('sell_price', [$lowFbaMin, $lowFbaMax]);
            }

            // 90 Day Avg Filter
            if ($request->has(['ninety_day_avg_min', 'ninety_day_avg_max']) && $request->input('ninety_day_avg_min') !== null && $request->input('ninety_day_avg_max') !== null) {
                $ninetyDayAvgMin = $request->input('ninety_day_avg_min');
                $ninetyDayAvgMax = $request->input('ninety_day_avg_max');
                $leads->whereBetween('bsr', [$ninetyDayAvgMin, $ninetyDayAvgMax]);
            }

            // Apply global search for DataTables
            if (request()->has('search') && !empty(request('search')['value'])) {
                $searchTerm = request('search')['value'];
        
                $leads->where(function ($query) use ($searchTerm) {
                    $query->where('source_id', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%")
                    ->orWhere('asin', 'like', "%{$searchTerm}%")
                    ->orWhere('supplier', 'like', "%{$searchTerm}%")
                    ->orWhere('category', 'like', "%{$searchTerm}%")
                    ->orWhere('notes', 'like', "%{$searchTerm}%")
                    ->orWhere('promo', 'like', "%{$searchTerm}%")
                    ->orWhere('coupon', 'like', "%{$searchTerm}%")
                    ->orWhereRaw("DATE_FORMAT(date, '%b %d, %Y') like ?", ["%{$searchTerm}%"]);
                });
            }

            return DataTables::of($leads)
                ->editColumn('actions', function ($row) {
                    $table ='table';
                    return '<div class="d-flex justify-content-between">
                        <div> <input type="checkbox" class="form-check" id="leadCheck'.$row->id.'" name="">  </div>
                        <div>
                        <button class="btn btn-light" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="mdi mdi-dots-vertical fs-5 ms-2"></i>
                    </button>
                    <div class="dropdown-menu">
                        <input type="hidden" name="asinTable'.$row->id.'"  id="asinDataTable'.$row->id.'" value="'.$row->asin.'">
                        <input type="hidden" name="nameTable'.$row->id.'"  id="nameDataTable'.$row->id.'" value="'.$row->name.'">
                        <a class="dropdown-item" style="cursor:pointer;" onclick="copyToClipBoard(' . $row->id . ', \'' . $table . '\')">
                            <i class="ri-file-copy-fill text-info me-2"></i>Copy to ClipBoard
                        </a>
                        <a class="dropdown-item" style="cursor:pointer;"  onclick="opneBuyListModal('.$row->id.')">
                            <i class="ri-money-dollar-box-line text-success me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add to Buy"></i> Add to Buylist
                        </a>
                        <a class="dropdown-item" style="cursor:pointer;"  onclick="openModal('.$row->id.')">
                            <i class="ri-share-box-fill text-success me-2"></i>Create New Order
                        </a>
                        <a class="dropdown-item" href="https://keepa.com/#!product/1-'.$row->asin.'" target="_blank" style="cursor:pointer;" >
                            <i class="ri-add-circle-fill text-primary me-2"></i>Keepa
                        </a>
                        <a class="dropdown-item" style="cursor:pointer;"  data-bs-toggle="modal" data-bs-target="#select-tag-modal"
                            onclick="asinNumber('.$row->asin.', '.$row->id.','.$row->tags.')">
                            <i class="ri-price-tag-3-fill text-primary me-2"></i>Change Tags
                        </a>
                        <a class="dropdown-item" style="cursor:pointer;"  data-bs-toggle="modal"
                            data-bs-target="#exampleModalScrollable"
                            onclick="leadFind('.$row->id.'), fetchSources('.$row->source_id.')">
                            <i class="ri-pencil-line text-primary me-2"></i>Update Lead
                        </a>
                        <a class="dropdown-item" style="cursor:pointer;"  onclick="leadDelete('.$row->id.')">
                            <i class="ri-delete-bin-line text-danger me-2"></i>Delete Lead
                        </a>
                    </div>
                        </div>
                    </div>'; // Example action button
                })
                ->editColumn('itemType', function ($row) {
                    $icons = '';

                    if ($row->bundle == 1) {
                        $icons .= '<i class="ri-add-circle-fill" id="bundleShow_' . $row->id . '" style="font-size:17px; cursor: pointer;" onclick="toggleBundle(' . $row->id . ', true)" title="Show Bundle Items"></i>';
                        $icons .= '<i class="ri-indeterminate-circle-fill" id="bundleHide_' . $row->id . '" style="font-size:17px; cursor: pointer; display: none;" onclick="toggleBundle(' . $row->id . ', false)" title="Hide Bundle Items"></i>';
                        $icons .= '<i class="ri-handbag-fill mx-1" title="Bundle" style="font-size:17px;"></i>';
                    }
        
                    if ($row->is_disputed == 1) {
                        $icons .= '<i class="text-danger ri-indeterminate-circle-fill" title="Item data may be disputed" style="font-size:17px;"></i>';
                    }
        
                    $icons .= '<i class="text-primary ri-user-add-fill" style="font-size:17px;" title="Your Uploaded Data"></i>';
        
                    if ($row->is_hazmat == 1) {
                        $icons .= '<i class="text-danger ri-alert-fill mx-1" title="Hazmat Item" style="font-size:17px;"></i>';
                    }
        
                    return $icons;
        
                })
                ->editColumn('name', function ($row) {
                    return $row->name; // Example action button
                })
                ->editColumn('tags', function ($lead) {
                    $tagsHtml = '<div class="tags"><div id="leadTag_' . $lead->id . '" style="display: inline;">';
            
                    if ($lead->lead_tags->isNotEmpty()) {
                        foreach ($lead->lead_tags as $tag) {
                            $tagsHtml .= '<span class="badge bg-' . $tag->color . '">' . $tag->name . '</span>';
                        }
                    }
            
                    $tagsHtml .= '</div>';
                    $tagsHtml .= '<a class="ms-2 text-primary" style="display: inline;" data-bs-toggle="modal" data-bs-target="#select-tag-modal" onclick="asinNumber(\'' . $lead->asin . '\', ' . $lead->id . ', \'' . $lead->tags . '\')" style="cursor: pointer;"><b>+ Add Tags</b></a>';
                    $tagsHtml .= '</div>';
            
                    return $tagsHtml;
                })
                ->editColumn('date', function ($row) {
                    return  Carbon::parse($row->date)->format('M jS, Y') .'<br><span><b>'. $row->source->list_name.'</b></span>' ; // Example action button
                })
                ->editColumn('cost', function ($row) {
                    return $row->cost !== null ? '$' . $row->cost : 'N/A'; // Show cost or N/A
                })
                ->editColumn('sale_price', function ($row) {
                    return $row->sell_price !== null ? '$' . $row->sell_price : 'N/A'; // Show sale price or N/A
                })
                ->editColumn('net_profit', function ($row) {
                    return $row->net_profit !== null ? '$' . $row->net_profit : 'N/A'; // Show net profit or N/A
                })
                ->editColumn('roi', function ($row) {
                    return $row->roi !== null ? $row->roi . '%' : 'N/A'; // Show RIO or N/A
                })
                ->editColumn('bsr', function ($row) {
                    return $row->bsr !== null ? $row->bsr : 'N/A'; // Show BSR or N/A
                })
                ->editColumn('category', function ($row) {
                    return $row->category !== null ? $row->category : 'N/A'; // Show category or N/A
                })            
                ->editColumn('supplier', function ($row) {
                    $icon ='';
                    if($row->url != null){
                        $icon ='<a href="'.$row->url.'" class="mt-3" target="_blank">
                        &lt;
                        <i class="ri-external-link-line text-primary fs-4"></i>
                        &gt;
                    </a>';
                    }
                    return $icon.'<br> <a href="'.$row->url.'" class="mt-3">'. $row->supplier.'</a> ';
                })
                ->editColumn('asin', function ($row) {
                    return '<a href="https://www.amazon.com/dp/'.$row->asin.'" class="mt-3"  target="_blank">
                    &lt;
                        <i class="ri-external-link-line text-primary fs-4"></i>
                    &gt;
                    '. $row->asin.'
                    </a> '
                    ; // Example action button
                })
                ->editColumn('promo', function ($row) {
                    return $row->promo !== null ? $row->promo : 'N/A'; // Show promo or N/A
                })
                ->editColumn('coupon', function ($row) {
                    return $row->coupon !== null ? $row->coupon : 'N/A'; // Show coupon or N/A
                })
                ->editColumn('notes', function ($row) {
                    return $row->notes !== null ? $row->notes : 'N/A'; // Show notes or N/A
                })            
                ->rawColumns(['actions','itemType','name','tags','date','cost','sale_price','net_profit','rio','bsr','category','supplier','asin','promo','coupon','notes'])  // Specify raw HTML columns
                ->make(true);
        }
        function generateMSKU(Request $request)
        {
            $asin = $request->asin;
            // Check if an MSKU already exists for the given ASIN
            $existingMsku = Lead::where('asin', $asin)->value('msku');

            if ($existingMsku) {
                return response()->json($existingMsku); // Return the existing MSKU if found
            }
            // Get the last MSKU from the database
            $lastMsku = Lead::orderBy('id', 'desc')->value('msku');
            if (!empty($lastMsku)) {
                $newMsku = "B000001";
            } else {
                // Get all existing MSKUs and extract numeric values
                $existingMskuNumbers = Lead::whereNotNull('msku')->pluck('msku')->map(function ($msku) {
                    return (int)substr($msku, 1); // Extract numeric part, remove 'B'
                })->unique()->sort()->values()->toArray(); // Remove duplicates, sort, and reset indices

                // Find the first missing number in sequence
                $newMskuNumber = 1;
                sort($existingMskuNumbers); // Ensure numbers are sorted
                foreach ($existingMskuNumbers as $number) {
                    if ($number != $newMskuNumber) {
                        break; // Found the first missing number
                    }
                    $newMskuNumber++;
                }

                // Generate new MSKU
                $newMsku = "B" . str_pad($newMskuNumber, 6, "0", STR_PAD_LEFT);

            }
            $existingMsku = Lead::where('asin', $asin)->update([
                'msku' => $newMsku
            ]);
            return response()->json($newMsku);
        }
        function getProductByAsin($asin) {
            $productData = $this->keepaService->getProductDetails($asin);
            dd($productData );
            
        }  
        public function rejectLead(Request $request)
        {
            $lead = Lead::find($request->id);

            if (!$lead) {
                return response()->json([
                    'status' => false,
                    'message' => 'Lead not found.'
                ]);
            }

            $lead->is_rejected = 1; // Assuming you have a `status` field
            $lead->save();

            return response()->json([
                'status' => true,
                'message' => 'Lead has been rejected successfully.'
            ]);
        }

    }
