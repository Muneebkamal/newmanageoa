<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Source;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!\Auth::user()->can('view_employees')) {
            abort(403);
        }
        // Retrieve all employees
        if ($request->ajax()) {
            $employees = User::select(['id', 'first_name', 'last_name', 'name', 'email', 'department_id', 'status', 'role_id']);
    
            return DataTables::of($employees)
                ->addIndexColumn() // Adds the loop iteration column
                ->addColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    if ($row->role_id != 1) {
                        $editUrl = route('employees.edit', $row->id);
                        $deleteUrl = route('employees.destroy', $row->id);
    
                        return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>
                            <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
                            </form>';
                    }
                    return '';
                })
                ->rawColumns(['status', 'actions']) // Ensures HTML is rendered
                ->make(true);
        }
        return view('employees.index'); // Return the view        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!\Auth::user()->can('view_employees')) {
            abort(403);
        }
        $roles = Role::all();
        return view('employees.add-employee',get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'department_id' => 'required',
        ]);
        $userRole = Role::where('id', $request->role_id)->first();

        // Create employee record
       $employeeUser = User::create([
            'role' => 2,
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->password),
            'department_id' => $request->input('department_id'),
            'status' => $request->status, // Convert status to boolean
            'role_id' => $userRole->id,
            'sync_lead_url' => $userRole->sync_lead_url
        ]);
        $employeeUser->assignRole($userRole->name);
        $this->getEmployeeLeads($employeeUser->id);

        // Redirect back with success message
        return redirect()->route('employees.index')->with('success', 'Employee added successfully!');

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
        if (!\Auth::user()->can('view_employees')) {
            abort(403);
        }
        $roles = Role::all();
        // Find employee by ID
        $employee = User::findOrFail($id);

        // Get all permissions and roles
        $permissions = Permission::all();
        $roles = Role::all();
        // Get the employee's current permissions
        $employeePermissions = $employee->getAllPermissions()->pluck('name')->toArray();
         // Pass data to the view
         return view('employees.edit-employee', compact('employee', 'permissions', 'roles', 'employeePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = User::findOrFail($id);

        // Validate the request data
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'role_id' => 'nullable|exists:roles,id', // Ensure the role_id exists in the roles table
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name', // Ensure permissions exist by name
        ]);

        // Update the employee data
        $employee->name = $request->name;
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->sync_lead_url = $request->sync_lead_url;
        $employee->send_email = isset($request->send_email) ? 1 : 0;

        // Check if password is provided and update it
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }

        $employee->department_id = $request->department_id;
        $employee->status = $request->status;
        $employee->save();

        // Sync employee permissions if provided
        if ($request->has('permissions')) {
            $permissions = $request->input('permissions'); // Assuming permission names are passed
            if ($request->filled('permissions')) { 
                $permissions = $request->input('permissions');
                $employee->syncPermissions([]); // Clear all first
                $employee->syncPermissions($permissions);
                $employee->load('permissions'); // Force reload from DB
            }
        } else {
            // Clear all permissions if none are provided
            $employee->syncPermissions([]);
        }
        // $this->getEmployeeLeads($employee->id);
        return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    // public function syncEmployeeLeadsCron(){
    //     $employees = User::whereNotNull('sync_lead_url')->get();
    //     foreach($employees as $employee){
    //         if($employee->sync_lead_url != null){
    //             $checkSource = Source::where('employee_id',$employee->id)->first();
    //             if($checkSource){
    //                 $source_id = $checkSource->id;
    //             }else{
    //                 $newSource = new Source;
    //                 $newSource->list_name = $employee->name;
    //                 $newSource->employee_id = $employee->id;
    //                 $newSource->save();
    //                 $source_id = $newSource->id;
    //             }
    //             // URL to the Google Sheet's CSV export
    //             // Provided Google Sheets URL
    //             $sync_lead_url = $employee->sync_lead_url;
    //             // Get Sheet ID from URL
    //             $sheetId = null;
    //             if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $sync_lead_url, $match)) {
    //                 $sheetId = $match[1];
    //             }
    //             if (!$sheetId) {
    //                 return response()->json(['error' => 'Invalid Sheet URL'], 400);
    //             }
    //             $tabs = $this->getGoogleSheetTabs($sheetId);
    //             if (empty($tabs)) {
    //                 return response()->json(['error' => 'No tabs found'], 400);
    //             }
    //             $source_id = $checkSource->id;
    //             $insertedCount = 0;
    //             $chunkSize = 50;
    //             foreach ($tabs as $tab) {
    //                 $gid = $tab['gid'];
    //                 $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

    //                 $response = Http::get($csvUrl);
    //                 if (!$response->successful()) continue;

    //                 $csvData = $response->body();
    //                 $rows = $this->parseCsv($csvData);
    //                 $chunks = array_chunk($rows, $chunkSize);

    //                 foreach ($chunks as $chunk) {
    //                     foreach ($chunk as $item) {
    //                         if (!empty($item['Product Name'])) {
    //                             if($item['Product Name'] != null){
    //                                 $possibleQuantityKeys = [
    //                                     'suggested buy trial quantity',
    //                                     'quantity',
    //                                     'qty',
    //                                     'trial quantity',
    //                                     'buy qty',
    //                                     'buy quantity'
    //                                 ];

    //                                 $quantity = 0;

    //                                 // Normalize the $item keys to lowercase for flexible matching
    //                                 $lowerItem = array_change_key_case($item, CASE_LOWER);

    //                                 // Loop through lowercase keys and get the matching value
    //                                 foreach ($possibleQuantityKeys as $key) {
    //                                     if (!empty($lowerItem[$key]) && is_numeric($lowerItem[$key])) {
    //                                         $quantity = (int) $lowerItem[$key];
    //                                         break;
    //                                     }
    //                                 }
    //                                 $data = [
    //                                     'source_id'=> $source_id,
    //                                     'date'=>date('Y-m-d H:i:s', strtotime($item['Date'])),
    //                                     'name'=>$item['Product Name'],
    //                                     'cost'=>$item['Cost'],
    //                                     'asin'=>$item['ASIN'],
    //                                     'url'=>$item['Source URL'],
    //                                     'supplier'=>$item['Source Site'],
    //                                     'category'=>$item['Category'],
    //                                     'sell_price'=>$item['Current BB Price'],
    //                                     'net_profit'=>$item['Net Profit'],
    //                                     'roi'=>$item['ROI'],
    //                                     'notes'=>$item['Notes'],
    //                                     'promo'=>$item['Promo/Coupon Code'],
    //                                     'quantity'=> $quantity,
    //                                 ];
    //                                 $priceFields = ['cost', 'sell_price', 'net_profit'];
    //                                 foreach ($priceFields as $field) {
    //                                     if (isset($data[$field])) {
    //                                         // Remove the $ sign
    //                                         $data[$field] = str_replace('$', '', $data[$field]);
    //                                         // Keep only numbers and decimals
    //                                         $data[$field] = preg_replace('/[^\d.]/', '', $data[$field]);
    //                                         // If the value is empty or null, set it to 0
    //                                         $data[$field] = $data[$field] === '' ? 0 : $data[$field];
    //                                     } else {
    //                                         // If the key is missing, set default value to 0
    //                                         $data[$field] = 0;
    //                                     }
    //                                 }
    //                                 if(isset($data['roi'])){
    //                                     $data['roi'] = str_replace('%', '', $data['roi']);
    //                                 }
    //                                 $data['sell_price']  = $data['sell_price'] ==""?0:$data['sell_price'];  
    //                                     // **Check if record already exists**
    //                                 $existingLead = Lead::where('date', $data['date'])
    //                                 ->where('name', $data['name'])
    //                                 ->where('asin', $data['asin'])
    //                                 ->where('source_id', $data['source_id'])
    //                                 ->first();
    //                                 if (!$existingLead) {
    //                                     Lead::create($data);
    //                                     $insertData[] = $data;
    //                                     $insertedCount += count($insertData);

    //                                 }else{
                                        
    //                                     $existingLead->quantity = $quantity;
    //                                     $existingLead->save();
    //                                 }
                                
    //                             }
    //                         }
    //                     }
    //                 }
                    
    //             }

    //             // return response()->json([
    //             //     'success' => true,
    //             //     'tabs_found' => count($tabs),
    //             //     'inserted_count' => $insertedCount,
    //             // ]);

    //             // // Parse the URL to extract the sheet ID
    //             // $parsedUrl = parse_url($googleSheetUrl);
    //             // $path = $parsedUrl['path']; // Path contains the sheet ID
    //             // $pathParts = explode('/', $path); // Split by '/'
                
    //             // // The sheet ID will be the second part in the URL path (after /d/)
    //             // $sheetId = $pathParts[3];
    //             // $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv";

    //             // // Fetch the CSV data from Google Sheets
    //             // $response = Http::get($url);
    //             // if ($response->successful()) {
    //             //     // Get the CSV data
    //             //     $csvData = $response->body();
    //             //     $rows = $this->parseCsv($csvData);
    //             //     \Log::info($rows );
    //             //     foreach($rows as $index => $item){
    //             //         if($item['Product Name'] != null){
    //             //             $data = [
    //             //                 'source_id'=> $source_id,
    //             //                 'date'=>date('Y-m-d H:i:s', strtotime($item['Date'])),
    //             //                 'name'=>$item['Product Name'],
    //             //                 'cost'=>$item['Cost'],
    //             //                 'asin'=>$item['ASIN'],
    //             //                 'url'=>$item['Source URL'],
    //             //                 'supplier'=>$item['Source Site'],
    //             //                 'category'=>$item['Category'],
    //             //                 'sell_price'=>$item['Current BB Price'],
    //             //                 'net_profit'=>$item['Net Profit'],
    //             //                 'roi'=>$item['ROI'],
    //             //                 'notes'=>$item['Notes'],
    //             //                 'promo'=>$item['Promo/Coupon Code'],
    //             //             ];
    //             //             $priceFields = ['cost', 'sell_price', 'net_profit'];
    //             //             foreach ($priceFields as $field) {
    //             //                 if (isset($data[$field])) {
    //             //                     // Remove the $ sign
    //             //                     $data[$field] = str_replace('$', '', $data[$field]);
    //             //                     // Keep only numbers and decimals
    //             //                     $data[$field] = preg_replace('/[^\d.]/', '', $data[$field]);
    //             //                     // If the value is empty or null, set it to 0
    //             //                     $data[$field] = $data[$field] === '' ? 0 : $data[$field];
    //             //                 } else {
    //             //                     // If the key is missing, set default value to 0
    //             //                     $data[$field] = 0;
    //             //                 }
    //             //             }
    //             //             if(isset($data['roi'])){
    //             //                 $data['roi'] = str_replace('%', '', $data['roi']);
    //             //             }
    //             //             $data['sell_price']  = $data['sell_price'] ==""?0:$data['sell_price'];  
    //             //                 // **Check if record already exists**
    //             //             $existingLead = Lead::where('date', $data['date'])
    //             //             ->where('name', $data['name'])
    //             //             ->where('asin', $data['asin'])
    //             //             ->where('source_id', $data['source_id'])
    //             //             ->exists();
    //             //             if (!$existingLead) {
    //             //                 Lead::create($data);
    //             //             }
                      
    //             //         }
                      
    //             //     }
    //             } else {
    //                 // Handle failure
    //                 return response()->json(['error' => 'Failed to fetch data from Google Sheets'], 500);
    //             }
    //         }
    // }
    public function syncEmployeeLeadsCron()
    {
        $employees = User::whereNotNull('sync_lead_url')->get();

        foreach ($employees as $employee) {
            $insertedCount = 0;
            $insertData = [];

            $checkSource = Source::where('employee_id', $employee->id)->first();

            if (!$checkSource) {
                $checkSource = new Source;
                $checkSource->list_name = $employee->name;
                $checkSource->employee_id = $employee->id;
                $checkSource->save();
            }

            $source_id = $checkSource->id;
            $sync_lead_url = $employee->sync_lead_url;

            // Extract Sheet ID
            $sheetId = null;
            if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $sync_lead_url, $match)) {
                $sheetId = $match[1];
            }

            if (!$sheetId) {
                \Log::warning("Invalid Google Sheet URL for user: {$employee->id}");
                continue;
            }

            $tabs = $this->getGoogleSheetTabs($sheetId);
            if (empty($tabs)) {
                \Log::warning("No tabs found in sheet for user: {$employee->id}");
                continue;
            }

            $chunkSize = 50;

            foreach ($tabs as $tab) {
                $gid = $tab['gid'];
                $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
                $response = Http::get($csvUrl);
                if (!$response->successful()) continue;

                $csvData = $response->body();
                $rows = $this->parseCsv($csvData);
                $chunks = array_chunk($rows, $chunkSize);

                foreach ($chunks as $chunk) {
                    foreach ($chunk as $item) {
                        if (!empty($item['Product Name'])) {
                            $possibleQuantityKeys = [
                                'suggested buy trial quantity',
                                'quantity',
                                'qty',
                                'trial quantity',
                                'buy qty',
                                'buy quantity'
                            ];

                            $quantity = 0;
                            $lowerItem = array_change_key_case($item, CASE_LOWER);

                            foreach ($possibleQuantityKeys as $key) {
                                if (!empty($lowerItem[$key]) && is_numeric($lowerItem[$key])) {
                                    $quantity = (int)$lowerItem[$key];
                                    break;
                                }
                            }

                            $data = [
                                'source_id' => $source_id,
                                'date' => date('Y-m-d H:i:s', strtotime($item['Date'])),
                                'name' => $item['Product Name'],
                                'cost' => $item['Cost'],
                                'asin' => $item['ASIN'],
                                'url' => $item['Source URL'],
                                'supplier' => $item['Source Site'],
                                'category' => $item['Category'],
                                'sell_price' => $item['Current BB Price'],
                                'net_profit' => $item['Net Profit'],
                                'roi' => $item['ROI'],
                                'notes' => $item['Notes'],
                                'promo' => $item['Promo/Coupon Code'],
                                'quantity' => $quantity,
                            ];

                            // Clean price fields
                            $priceFields = ['cost', 'sell_price', 'net_profit'];
                            foreach ($priceFields as $field) {
                                $data[$field] = isset($data[$field])
                                    ? preg_replace('/[^\d.]/', '', str_replace('$', '', $data[$field]))
                                    : 0;
                                $data[$field] = $data[$field] === '' ? 0 : $data[$field];
                            }

                            if (isset($data['roi'])) {
                                $data['roi'] = str_replace('%', '', $data['roi']);
                            }

                            $data['sell_price'] = $data['sell_price'] == "" ? 0 : $data['sell_price'];

                            // Check for existing lead
                            $existingLead = Lead::where('asin', $data['asin'])
                                // where('date', $data['date'])
                                // ->where('name', $data['name'])
                                ->where('source_id', $data['source_id'])
                                ->first();

                            if (!$existingLead) {
                                Lead::create($data);
                                $insertData[] = $data;
                                $insertedCount++;
                            } else {
                                // $existingLead->quantity = $quantity;
                                // $existingLead->save();
                            }
                        }
                    }
                }
            }

            // Optional: Send summary email to admin
            // if ($insertedCount > 0) {
            //     $body = "<h2>Leads Uploaded for {$employee->name}</h2>";
            //     $body .= "<p><strong>Total Leads Inserted:</strong> {$insertedCount}</p>";
            //     $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
            //     $body .= "<tr><th>Date</th><th>Name</th><th>ASIN</th><th>ASIN</th><th>Cost</th><th>Sell Price</th><th>Profit</th><th>ROI</th> </tr>";
            //     foreach (array_slice($insertData, 0, 10) as $lead) {
            //         $currentDateEST = (new \DateTime('now', new \DateTimeZone('America/New_York')))->format('Y-m-d h:i A');
            //         $body .= "<tr>
            //             <td>
            //                 <a href='" . url('leads-new?asin=' . $lead['asin']) . "' target='_blank'>
            //                     {$currentDateEST}
            //                 </a>
            //             </td>
            //             <td>{$lead['name']}</td>
            //             <td>
            //                 <a href='https://www.amazon.com/dp/{$lead['asin']}' target='_blank'>
            //                     {$lead['asin']}
            //                 </a>
            //             </td>
            //             <td>
            //                 <a href='{$lead['url']}' target='_blank'>
            //                     {$lead['supplier']}
            //                 </a>
            //             </td>
            //             <td>\${$lead['cost']}</td>
            //             <td>\${$lead['sell_price']}</td>
            //             <td>\${$lead['net_profit']}</td>
            //             <td>{$lead['roi']}%</td>
                        
            //         </tr>";
            //     }
            //     $body .= "</table>";
            //     $mail = new MailController();
            //     $mail->sendEmail(
            //         "Lead Upload Summary for {$employee->name}",
            //         $body,
            //         'dailyreport@znzinc.com', // Replace with admin email
            //         'Admin'
            //     );
            // }

            \Log::info("Lead sync completed for {$employee->name}. Inserted: {$insertedCount}");
        }

        return response()->json(['success' => true, 'message' => 'Cron finished syncing leads.']);
    }
    public function sendDailyEmailCron(){
        $employees = User::with('source')
        ->whereNotNull('sync_lead_url')
        ->get();
        //Get today's date
        $start = Carbon::yesterday('America/New_York')->setTime(8, 0); // Yesterday 8 AM EST
        $end = Carbon::today('America/New_York')->setTime(8, 0);       // Today 8 AM EST
        $leads = Lead::whereBetween('created_at', [$start, $end])->get()->groupBy('source_id');
        foreach ($employees as $employee)
        {
            $sendMail = 0;
            $employeeEmail = $employee->email;
            if($employee->send_email == 1){
                $sendMail = 1;
            }
            $source = $employee->source;
            $sourceId = $source?->id;
            $employeeLeads = $leads[$sourceId] ?? collect();
            if ($source && $employeeLeads->isNotEmpty()){
                $body = "<h2>Leads Uploaded for {$employee->name}</h2>";
                $body .= "<p><strong>Total Leads Inserted:</strong> " . count($employeeLeads) . " <strong>Date:</strong> " . Carbon::today('America/New_York')->format('Y-m-d') . "</p>";
                $body .= "<p></p>";
                $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
                $body .= "<tr><th>Date</th><th>Name</th><th>ASIN</th><th>Source</th><th>Cost</th><th>Sell Price</th><th>Profit</th><th>ROI</th> </tr>";
                foreach ($employeeLeads as $lead) {
                    $currentDateEST = (new \DateTime($lead->created_at, new \DateTimeZone('America/New_York')))->format('Y-m-d h:i A');
                    $body .= "<tr>
                        <td>
                            <a href='" . url('leads-new?asin=' . $lead['asin']) . "' target='_blank'>
                                {$currentDateEST}
                            </a>
                        </td>
                        <td>{$lead->name}</td>
                        <td>
                            <a href='https://www.amazon.com/dp/{$lead->asin}' target='_blank'>
                                {$lead->asin}
                            </a>
                        </td>
                        <td>
                            <a href='{$lead->url}' target='_blank'>
                                {$lead->supplier}
                            </a>
                        </td>
                        <td>\${$lead->sell_price}</td>
                        <td>\${$lead->cost}</td>
                        <td>\${$lead->net_profit}</td>
                        <td>{$lead->roi}%</td>
                        
                    </tr>";
                }
                $body .= "</table>";
                if($sendMail == 1){
                    // Send email to admin
                    $mail = new MailController();
                    $mail->sendEmail(
                        "Lead Upload Summary for {$employee->name}",
                        $body,
                        $employeeEmail, // Employee's email
                        $employee->name // Employee's name
                    );
                }
                // Second Table (Not in buylist & Not rejected)
                $extraLeads =  Lead::query()
                ->where('is_rejected', 0)
                ->where('source_id', $sourceId)
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotIn('asin', function ($query) {
                    $query->select('asin')
                        ->from('line_items');
                })
                ->orderByDesc('created_at')
                ->take(30)
                ->get();


                if ($extraLeads->isNotEmpty()) {
                    $body .= "<br><h3>Leads Not in Buylist & Not Rejected</h3>";
                    $body .= "<p><strong>Last " . count($extraLeads) . " Leads</strong>  </p>";
                    $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
                    $body .= "<tr><th>Date</th><th>Name</th><th>ASIN</th><th>Source</th><th>Cost</th><th>Sell Price</th><th>Profit</th><th>ROI</th></tr>";

                    foreach ($extraLeads as $lead) {
                        $currentDateEST = (new \DateTime($lead->created_at, new \DateTimeZone('America/New_York')))->format('Y-m-d h:i A');
                        $body .= "<tr>
                            <td><a href='" . url('leads-new?asin=' . $lead['asin']) . "' target='_blank'>{$currentDateEST}</a></td>
                            <td>{$lead->name}</td>
                            <td><a href='https://www.amazon.com/dp/{$lead->asin}' target='_blank'>{$lead->asin}</a></td>
                            <td><a href='{$lead->url}' target='_blank'>{$lead->supplier}</a></td>
                            <td>\${$lead->sell_price}</td>
                            <td>\${$lead->cost}</td>
                            <td>\${$lead->net_profit}</td>
                            <td>{$lead->roi}%</td>
                        </tr>";
                    }

                    $body .= "</table>";
                }
                $mail = new MailController();
                $mail->sendEmail(
                    "Lead Upload Summary for {$employee->name}",
                    $body,
                    'dailyreport@znzinc.com', // Replace with admin email
                    'Admin'
                );
            }else{
                $mail = new MailController();
                $body = "<h2>No Leads Uploaded for {$employee->name}</h2>";
                if($sendMail == 1){
                    // Send email to admin
                    $mail = new MailController();
                    $mail->sendEmail(
                        "Lead Upload Summary for {$employee->name}",
                        $body,
                        $employeeEmail, // Employee's email
                        $employee->name // Employee's name
                    );
                }
                // Second Table (Not in buylist & Not rejected)
                $extraLeads =  Lead::query()
                ->where('is_rejected', 0)
                ->where('source_id', $sourceId)
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotIn('asin', function ($query) {
                    $query->select('asin')
                    ->from('line_items');
                })
                ->orderByDesc('created_at')
                ->take(30)
                ->get();


                if ($extraLeads->isNotEmpty()) {
                    $body .= "<br><h3>Leads Not in Buylist & Not Rejected</h3>";
                    $body .= "<p><strong>Last " . count($extraLeads) . " Leads</strong>  </p>";
                    $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
                    $body .= "<tr><th>Date</th><th>Name</th><th>ASIN</th><th>Source</th><th>Cost</th><th>Sell Price</th><th>Profit</th><th>ROI</th></tr>";

                    foreach ($extraLeads as $lead) {
                        $currentDateEST = (new \DateTime($lead->created_at, new \DateTimeZone('America/New_York')))->format('Y-m-d h:i A');
                        $body .= "<tr>
                            <td><a href='" . url('leads-new?asin=' . $lead['asin']) . "' target='_blank'>{$currentDateEST}</a></td>
                            <td>{$lead->name}</td>
                            <td><a href='https://www.amazon.com/dp/{$lead->asin}' target='_blank'>{$lead->asin}</a></td>
                            <td><a href='{$lead->url}' target='_blank'>{$lead->supplier}</a></td>
                            <td>\${$lead->sell_price}</td>
                            <td>\${$lead->cost}</td>
                            <td>\${$lead->net_profit}</td>
                            <td>{$lead->roi}%</td>
                        </tr>";
                    }

                    $body .= "</table>";
                }
                $mail->sendEmail(
                    "Lead Upload Summary for {$employee->name}",
                    $body,
                    'dailyreport@znzinc.com', // Replace with admin email
                    'Admin'
                );
            }
            \Log::info("Lead sync completed for {$employee->name}");
        }
    }

    // Helper function to parse CSV data
    private function parseCsv($csvData)
    {
        $lines = explode("\n", trim($csvData));

        if (empty($lines)) {
            return [];
        }

        // Extract headers
        $csvHeaders = str_getcsv(array_shift($lines)); 

        // Initialize an array to store parsed data
        $csvData = [];

        // Read and parse CSV data row by row
        foreach ($lines as $line) {
            $row = str_getcsv($line);

            // Skip empty rows
            if (!array_filter($row)) {
                continue;
            }

            $csvData[] = $row;
        }

        // Initialize an array to hold the CSV data
        // Process CSV data to match headers
        $data = [];
        foreach ($csvData as $row) {
            // Adjust the row to match the header length
            if (count($row) < count($csvHeaders)) {
                $row = array_pad($row, count($csvHeaders), null);
            } elseif (count($row) > count($csvHeaders)) {
                $row = array_slice($row, 0, count($csvHeaders));
            }

            $data[] = array_combine($csvHeaders, $row);
        }

        return $data;
    }
    public function getEmployeeLeads($id){
        $employee = User::where('id',$id)->first();
        if($employee->sync_lead_url != null){
            $checkSource = Source::where('employee_id',$employee->id)->first();
            if($checkSource){
                $source_id = $checkSource->id;
            }else{
                $newSource = new Source;
                $newSource->list_name = $employee->first_name .' ' .$employee->last_name;
                $newSource->employee_id = $employee->id;
                $newSource->save();
                $source_id = $newSource->id;
            }
                // URL to the Google Sheet's CSV export
            // Provided Google Sheets URL
            $googleSheetUrl = $employee->sync_lead_url;
    
            // Parse the URL to extract the sheet ID
            $parsedUrl = parse_url($googleSheetUrl);
            $path = $parsedUrl['path']; // Path contains the sheet ID
            $pathParts = explode('/', $path); // Split by '/'
            
            // The sheet ID will be the second part in the URL path (after /d/)
            $sheetId = $pathParts[3];
            $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv";
    
            // Fetch the CSV data from Google Sheets
            $response = Http::get($url);
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch data from Google Sheets'], 500);
            }
            if ($response->successful()) {
                // Get the CSV data
                $csvData = $response->body();
                $rows = $this->parseCsv($csvData);
                $chunkSize = 50; // Adjust chunk size
                $chunks = array_chunk($rows, $chunkSize);
                $chunkCount = count($chunks);
                $insertedCount = 0;
                foreach($chunks as $index => $chunk){
                    $insertData = [];
                    foreach ($chunk as $item) {
                        if (!empty($item['Product Name'])) {
                            if($item['Product Name'] != null){
                                $data = [
                                    'source_id'=> $source_id,
                                    'date'=>date('Y-m-d H:i:s', strtotime($item['Date'])),
                                    'name'=>$item['Product Name'],
                                    'cost'=>$item['Cost'],
                                    'asin'=>$item['ASIN'],
                                    'url'=>$item['Source URL'],
                                    'supplier'=>$item['Source Site'],
                                    'category'=>$item['Category'],
                                    'sell_price'=>$item['Current BB Price'],
                                    'net_profit'=>$item['Net Profit'],
                                    'roi'=>$item['ROI'],
                                    'notes'=>$item['Notes'],
                                    'promo'=>$item['Promo/Coupon Code'],
                                ];
                                $priceFields = ['cost', 'sell_price', 'net_profit'];
                                foreach ($priceFields as $field) {
                                    if (isset($data[$field])) {
                                        // Remove the $ sign
                                        $data[$field] = str_replace('$', '', $data[$field]);
                                        // Keep only numbers and decimals
                                        $data[$field] = preg_replace('/[^\d.]/', '', $data[$field]);
                                        // If the value is empty or null, set it to 0
                                        $data[$field] = $data[$field] === '' ? 0 : $data[$field];
                                    } else {
                                        // If the key is missing, set default value to 0
                                        $data[$field] = 0;
                                    }
                                }
                                if(isset($data['roi'])){
                                    $data['roi'] = str_replace('%', '', $data['roi']);
                                }
                                $data['sell_price']  = $data['sell_price'] ==""?0:$data['sell_price'];  
                                    // **Check if record already exists**
                                $existingLead = Lead::where('asin', $data['asin'])
                                // where('date', $data['date'])
                                // ->where('name', $data['name'])
                                ->where('source_id', $data['source_id'])
                                ->exists();
                                if (!$existingLead) {
                                    Lead::create($data);
                                    $insertData[] = $data;
                                    $insertedCount += count($insertData);

                                }
                            
                            }
                        }
                    }
                    
                }
                return response()->json([
                    'success' => true,
                    'chunk_count' => $chunkCount,
                    'inserted_count' => $insertedCount,
                ]);
            } else {
                // Handle failure
                return response()->json(['error' => 'Failed to fetch data from Google Sheets'], 500);
            }
        }
    }
    // public function getEmployeeLeadsNew(Request $request,$id){
    //     $employee = User::where('id',$id)->first();
    //     $sync_lead_url = null;
    //     if (!is_null($employee->sync_lead_url)) {
    //         // Run next procedure here
    //         $sync_lead_url = $employee->sync_lead_url;
    //     }else if(!is_null($request->sync_lead_url)){
    //         $sync_lead_url = $request->sync_lead_url;
    //     }
    //     if($sync_lead_url != null){
    //         $checkSource = Source::where('employee_id',$employee->id)->first();
    //         if($checkSource){
    //             $source_id = $checkSource->id;
    //         }else{
    //             $newSource = new Source;
    //             $newSource->list_name = $employee->first_name .' ' .$employee->last_name;
    //             $newSource->employee_id = $employee->id;
    //             $newSource->save();
    //             $source_id = $newSource->id;
    //         }
    //             // URL to the Google Sheet's CSV export
    //         // Provided Google Sheets URL
    //         $googleSheetUrl = $sync_lead_url;
    //         $sheetUrl =  $googleSheetUrl; // pass URL in request
    
    //         // Parse the URL to extract the sheet ID
    //         $parsedUrl = parse_url($googleSheetUrl);
    //         $path = $parsedUrl['path']; // Path contains the sheet ID
    //         $pathParts = explode('/', $path); // Split by '/'
            
    //         // The sheet ID will be the second part in the URL path (after /d/)
    //         $sheetId = $pathParts[3];
    //         $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv";
    
    //         // Fetch the CSV data from Google Sheets
    //         $response = Http::get($url);
    //         if (!$response->successful()) {
    //             return response()->json(['error' => 'Failed to fetch data from Google Sheets'], 500);
    //         }
    //         if ($response->successful()) {
    //             // Get the CSV data
    //             $csvData = $response->body();
    //             $rows = $this->parseCsv($csvData);
    //             $chunkSize = 50; // Adjust chunk size
    //             $chunks = array_chunk($rows, $chunkSize);
    //             $chunkCount = count($chunks);
    //             $insertedCount = 0;
    //             foreach($chunks as $index => $chunk){
    //                 $insertData = [];
    //                 foreach ($chunk as $item) {
    //                     if (!empty($item['Product Name'])) {
    //                         if($item['Product Name'] != null){
    //                             $possibleQuantityKeys = [
    //                                 'suggested buy trial quantity',
    //                                 'quantity',
    //                                 'qty',
    //                                 'trial quantity',
    //                                 'buy qty',
    //                                 'buy quantity'
    //                             ];

    //                             $quantity = 0;

    //                             // Normalize the $item keys to lowercase for flexible matching
    //                             $lowerItem = array_change_key_case($item, CASE_LOWER);

    //                             // Loop through lowercase keys and get the matching value
    //                             foreach ($possibleQuantityKeys as $key) {
    //                                 if (!empty($lowerItem[$key]) && is_numeric($lowerItem[$key])) {
    //                                     $quantity = (int) $lowerItem[$key];
    //                                     break;
    //                                 }
    //                             }
    //                             $data = [
    //                                 'source_id'=> $source_id,
    //                                 'date'=>date('Y-m-d H:i:s', strtotime($item['Date'])),
    //                                 'name'=>$item['Product Name'],
    //                                 'cost'=>$item['Cost'],
    //                                 'asin'=>$item['ASIN'],
    //                                 'url'=>$item['Source URL'],
    //                                 'supplier'=>$item['Source Site'],
    //                                 'category'=>$item['Category'],
    //                                 'sell_price'=>$item['Current BB Price'],
    //                                 'net_profit'=>$item['Net Profit'],
    //                                 'roi'=>$item['ROI'],
    //                                 'notes'=>$item['Notes'],
    //                                 'promo'=>$item['Promo/Coupon Code'],
    //                                 'quantity'=> $quantity,
    //                             ];
    //                             $priceFields = ['cost', 'sell_price', 'net_profit'];
    //                             foreach ($priceFields as $field) {
    //                                 if (isset($data[$field])) {
    //                                     // Remove the $ sign
    //                                     $data[$field] = str_replace('$', '', $data[$field]);
    //                                     // Keep only numbers and decimals
    //                                     $data[$field] = preg_replace('/[^\d.]/', '', $data[$field]);
    //                                     // If the value is empty or null, set it to 0
    //                                     $data[$field] = $data[$field] === '' ? 0 : $data[$field];
    //                                 } else {
    //                                     // If the key is missing, set default value to 0
    //                                     $data[$field] = 0;
    //                                 }
    //                             }
    //                             if(isset($data['roi'])){
    //                                 $data['roi'] = str_replace('%', '', $data['roi']);
    //                             }
    //                             $data['sell_price']  = $data['sell_price'] ==""?0:$data['sell_price'];  
    //                                 // **Check if record already exists**
    //                             $existingLead = Lead::where('date', $data['date'])
    //                             ->where('name', $data['name'])
    //                             ->where('asin', $data['asin'])
    //                             ->where('source_id', $data['source_id'])
    //                             ->first();
    //                             if (!$existingLead) {
    //                                 Lead::create($data);
    //                                 $insertData[] = $data;
    //                                 $insertedCount += count($insertData);

    //                             }else{
                                   
    //                                 $existingLead->quantity = $quantity;
    //                                 $existingLead->save();
    //                             }
                            
    //                         }
    //                     }
    //                 }
                    
    //             }
    //             return response()->json([
    //                 'success' => true,
    //                 'chunk_count' => $chunkCount,
    //                 'inserted_count' => $insertedCount,
    //             ]);
    //         } else {
    //             // Handle failure
    //             return response()->json(['error' => 'Failed to fetch data from Google Sheets'], 500);
    //         }
    //     }
    // }
    public function getEmployeeLeadsNew(Request $request, $id)
    {
        $employee = User::findOrFail($id);
        $sync_lead_url = $employee->sync_lead_url ?? $request->sync_lead_url;
        if (!$sync_lead_url) {
            return response()->json(['error' => 'No sync URL found'], 400);
        }
        // Get Sheet ID from URL
        $sheetId = null;
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $sync_lead_url, $match)) {
            $sheetId = $match[1];
        }
        if (!$sheetId) {
            return response()->json(['error' => 'Invalid Sheet URL'], 400);
        }
        $tabs = $this->getGoogleSheetTabs($sheetId);
        if (empty($tabs)) {
            return response()->json(['error' => 'No tabs found'], 400);
        }
        $checkSource = Source::firstOrCreate(
            ['employee_id' => $employee->id],
            ['list_name' => $employee->first_name . ' ' . $employee->last_name]
        );
        $source_id = $checkSource->id;
        $insertedCount = 0;
        $chunkSize = 50;
        foreach ($tabs as $tab) {
            $gid = $tab['gid'];
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::get($csvUrl);
            if (!$response->successful()) continue;

            $csvData = $response->body();
            $rows = $this->parseCsv($csvData);
            $chunks = array_chunk($rows, $chunkSize);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $item) {
                    if (!empty($item['Product Name'])) {
                        if($item['Product Name'] != null){
                            $possibleQuantityKeys = [
                                'suggested buy trial quantity',
                                'quantity',
                                'qty',
                                'trial quantity',
                                'buy qty',
                                'buy quantity'
                            ];

                            $quantity = 0;

                            // Normalize the $item keys to lowercase for flexible matching
                            $lowerItem = array_change_key_case($item, CASE_LOWER);

                            // Loop through lowercase keys and get the matching value
                            foreach ($possibleQuantityKeys as $key) {
                                if (!empty($lowerItem[$key]) && is_numeric($lowerItem[$key])) {
                                    $quantity = (int) $lowerItem[$key];
                                    break;
                                }
                            }
                            $data = [
                                'source_id'=> $source_id,
                                'date'=>date('Y-m-d H:i:s', strtotime($item['Date'])),
                                'name'=>$item['Product Name'],
                                'cost'=>$item['Cost'],
                                'asin'=>$item['ASIN'],
                                'url'=>$item['Source URL'],
                                'supplier'=>$item['Source Site'],
                                'category'=>$item['Category'],
                                'sell_price'=>$item['Current BB Price'],
                                'net_profit'=>$item['Net Profit'],
                                'roi'=>$item['ROI'],
                                'notes'=>$item['Notes'],
                                'promo'=>$item['Promo/Coupon Code'],
                                'quantity'=> $quantity,
                            ];
                            $priceFields = ['cost', 'sell_price', 'net_profit'];
                            foreach ($priceFields as $field) {
                                if (isset($data[$field])) {
                                    // Remove the $ sign
                                    $data[$field] = str_replace('$', '', $data[$field]);
                                    // Keep only numbers and decimals
                                    $data[$field] = preg_replace('/[^\d.]/', '', $data[$field]);
                                    // If the value is empty or null, set it to 0
                                    $data[$field] = $data[$field] === '' ? 0 : $data[$field];
                                } else {
                                    // If the key is missing, set default value to 0
                                    $data[$field] = 0;
                                }
                            }
                            if(isset($data['roi'])){
                                $data['roi'] = str_replace('%', '', $data['roi']);
                            }
                            $data['sell_price']  = $data['sell_price'] ==""?0:$data['sell_price'];  
                                // **Check if record already exists**
                            $existingLead = Lead::where('asin', $data['asin'])
                                // where('date', $data['date'])
                            // ->where('name', $data['name'])
                            ->where('source_id', $data['source_id'])
                            ->first();
                            if (!$existingLead) {
                                Lead::create($data);
                                $insertData[] = $data;
                                $insertedCount += count($insertData);

                            }else{
                                
                                $existingLead->quantity = $quantity;
                                $existingLead->save();
                            }
                        
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'tabs_found' => count($tabs),
            'inserted_count' => $insertedCount,
        ]);
    }
    public function getGoogleSheetTabs($sheetId)
    {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}?fields=sheets.properties&key=AIzaSyAh1JAhw3XLl012NP8Q5v6JBYJjZVOULaM";

        $response = Http::get($url);

        if ($response->successful()) {
            $tabs = collect($response->json()['sheets'])->map(function ($sheet) {
                return [
                    'name' => $sheet['properties']['title'],
                    'gid' => $sheet['properties']['sheetId'],
                ];
            });

            return $tabs; // 🔁 Make sure to return this
        } else {
            return response()->json([
                'error' => 'Unable to fetch sheet tabs.',
                'details' => $response->body(),
            ], $response->status());
        }
    }
}
