<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Source;
use App\Models\Template;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!\Auth::user()->can('view_my_uploads')) {
            abort(403);
        }
        return view('uploads.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function sourceStore(Request $request)
    {
        $request->validate([
            'list_name' => ['required'],
        ]);

        $source = new Source;
        $source->list_name = $request->list_name;
        $source->save();

        $source_id = $source->id;
        return response()->json([
            'message' => 'Source added successfully!',
            'data' => $source_id
        ],);
    }

    public function getSources()
    {
        if(auth()->user()->role_id == 1){
            $sources = Source::orderBy('id', 'desc')->get();
        }else{
            $sources = Source::orderBy('id', 'desc')->where('employee_id',auth()->user()->id)->get();
        }
       
        return response()->json([
            'status' => 'success',
            'data' => $sources
        ]);
    }

    public function sourceFind($id)
    {
        $listName = Source::where('id', $id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $listName
        ]);
    }

    public function sourceUpdate(Request $request, $id) 
    {
        $source_id = $id;

        $source = Source::where('id', $id)->first();
        $source->list_name = $request->list_name;
        $source->save();

        return response()->json([
            'message' => 'Source update successfully!',
            'data' => $source_id
        ]);
    }

    public function sourceDelete($id)
    {
        $source = Source::where('id', $id)->first();
        $source->delete();

        return response()->json([
            'message' => 'Source Deleted successfully!'
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
    public function uploadFile(Request $request){
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);
    
        // Store the file in storage
        $filePath = $request->file('file')->store('uploads');
    
        // Get the full path of the uploaded file
        $fullFilePath = storage_path('app/' . $filePath);
    
        // Read the CSV file
        $file = fopen($fullFilePath, 'r');
        $headers = fgetcsv($file); // Get the first row as headers
    
        // Fetch database columns using your model
        $tableName = 'leads'; // Change to your actual table name
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
        
        // Filter out 'id', 'created_at', 'deleted_at', and 'updated_at'
        $excludedColumns = ['id', 'source_id', 'created_at', 'deleted_at', 'updated_at'];

        // Loop through each excluded column and unset it from the $columns array
        $excludedColumns = ['id', 'source_id', 'created_at', 'deleted_at', 'updated_at','bundle','createdBy','buyer_id','currency','is_hazmat','is_disputed','tags','msku','created_by'];

        // Loop through each excluded column and unset it from the $columns array
        foreach ($excludedColumns as $excluded) {
            if (($key = array_search($excluded, $columns)) !== false) {
                unset($columns[$key]); // Unset the column if found
            }
        }

        // Reindex the array
        $columns = array_values($columns);
    
        fclose($file);
    
        // Return a response with the headers, columns, and the file path
        return response()->json([
            'headers' => $headers,
            'columns' => $columns,
            'file_path' => $fullFilePath, // Include the full path of the file
        ]);
    }
    // public function getData(Request $request){
    //     // Validate the incoming request
    //     $request->validate([
    //         'column' => 'required|string',
    //         'file_path' => 'required|string',
    //     ]);
    //     $column = $request->input('column'); // Get the selected column
    //     $filePath = $request->input('file_path'); // Get the file path

    //     $data = []; // Initialize an array to hold the results

    //     // Open the CSV file for reading
    //     if (($handle = fopen($filePath, 'r')) !== false) {
    //         $headers = fgetcsv($handle); // Read the headers

    //         // Find the index of the selected column
    //         $columnIndex = array_search($column, $headers);

    //         if ($columnIndex !== false) {
    //             // Loop through the rows in the CSV file
    //             while (($row = fgetcsv($handle)) !== false) {
    //                 // Add the data for the selected column to the results
    //                 $data[] = $row[$columnIndex];
    //             }
    //         }

    //         fclose($handle); // Close the file
    //     }
    //     // Return the data as a JSON response
    //     return response()->json($data);
    // }
    public function getData(Request $request){
        // Validate the incoming request
        $request->validate([
            'column' => 'required|string',
            'file_path' => 'required|string',
        ]);
    
        $column = $request->input('column'); // Get the selected column
        // $filePath = storage_path('app/' . $request->input('file_path')); // Ensure the file path is correct
        $data = []; // Initialize an array to hold the results
    
        // Open the CSV file for reading
        if (!file_exists($request->file_path)) {
            return response()->json(['error' => 'File not found.'], 404);
        }
    
        if (($handle = fopen($request->file_path, 'r')) !== false) {
            // Read the headers
            $headers = fgetcsv($handle);
    
            if ($headers === false) {
                fclose($handle);
                return response()->json(['error' => 'Unable to read headers from the CSV file.'], 500);
            }
    
            // Find the index of the selected column
            $columnIndex = array_search($column, $headers);
    
            if ($columnIndex === false) {
                fclose($handle);
                return response()->json(['error' => 'Specified column not found.'], 404);
            }
            $recordCount =0;
            // Loop through the rows in the CSV file
            while (($row = fgetcsv($handle)) !== false) {
                // Check if the index exists in the row
                if (isset($row[$columnIndex])) {
                    $value = $row[$columnIndex];
    
                    // Check if the value is a string and attempt to sanitize it
                    if (is_string($value)) {
                        $value = trim($value);
                        
                        // Convert the encoding to UTF-8 if necessary
                        if (!mb_check_encoding($value, 'UTF-8')) {
                            $detectedEncoding = mb_detect_encoding($value, mb_detect_order(), true);
                            if ($detectedEncoding) {
                                $value = mb_convert_encoding($value, 'UTF-8', $detectedEncoding);
                            } else {
                                $value = null; // Set to null if encoding cannot be detected
                            }
                        }
                    } else {
                        $value = null; // Set to null if not a string
                    }
    
                    // Add the cleaned value to the results
                    $data[] = $value;
                    $recordCount++;
    
                    // Break the loop after collecting the first 5 records
                    if ($recordCount >= 5) {
                        break;
                    }
                }
            }
    
            fclose($handle); // Close the file
        } else {
            return response()->json(['error' => 'Could not open file.'], 500);
        }
    
        // Return the data as a JSON response
        return response()->json($data);
    }

    public function saveTemplate(Request $request) {
        // Check if the template with the same name already exists
        $templateExists = Template::where('name', $request->name)->exists();

        if ($templateExists) {
            // If template already exists, return a response with 'exists' flag
            return response()->json(['exists' => true]);
        }

        // Proceed to save the template if it doesn't exist
        $template = new Template();
        $template->name = $request->name;
        $template->db_columns = json_encode($request->mapped_columns); // Assuming db_columns are stored as JSON
        $template->mapping_template = json_encode($request->db_columns); // Assuming mapped columns are stored as JSON
        $template->save();
        // Return success response
        return response()->json(['exists' => false, 'message' => 'Template saved successfully']);
    }
    public function getTemplates(){
        $templaes = Template::latest('created_at')->get();
        return response()->json($templaes);
    }
    public function getTemplateMapping(Request $request){
        // Find the template by ID
        $template = Template::find($request->id);

        // Check if the template exists
        if (!$template) {
            return response()->json([
                'error' => 'Template not found'
            ], 404);
        }

        // Assuming mappings are stored as JSON in the 'mapping_template' column of the 'templates' table
        $mappings = json_decode($template->mapping_template, true);

        // Retrieve the file path from the request or template (assuming file_path is stored in the template or request)
        $filePath = $request->path ?? $template->file_path;
        // Retrieve the file data based on the mappings
        $fileData = $this->getFileData($filePath, $mappings);
        // dd( $fileData);

        // Return both mappings and file data
        return response()->json([
            'mappings' => $mappings,
            'file_data' => $fileData
        ]);
    }

    // Helper method to retrieve the file data
    // protected function getFileData($filePath, $mappings)
    // {
    //     // Assume the file is CSV and located in storage (adjust according to your file type and location)
    //     $file = storage_path('app/' . $filePath);
    //     $fileData = [];
    //     if (($handle = fopen($filePath, 'r')) !== false) {
    //         $headers = fgetcsv($handle); // Read the headers

    //         // Create an associative array for header indices for easy lookup
    //         $headerIndices = array_flip($headers); // This will help map headers to their indices
        
    //         // Loop through the CSV rows
    //         while (($row = fgetcsv($handle)) !== false) {
    //             $mappedRow = [];
        
    //             // Iterate through the headers and map the values
    //             foreach ($headers as $header) {
    //                 $headerIndex = $headerIndices[$header]; // Get the index of the current header
    //                 $mappedRow[$header] = isset($row[$headerIndex]) ? $row[$headerIndex] : null; // Map the value or set to null
    //             }
        
    //             $fileData[] = $mappedRow; // Add the mapped row to the file data
    //         }

    //         fclose($handle); // Close the file
    //     }
    //     return $fileData;
    // }
    protected function getFileData($filePath, $mappings)
{
    // Get the full path of the file in storage
    $file = storage_path('app/' . $filePath);
    $fileData = [];

    // Open the file in binary mode
    if (($handle = fopen($filePath, 'rb')) !== false) {
        // Read the headers from the CSV file
        $headers = fgetcsv($handle);

        if ($headers === false) {
            error_log("Error reading headers from CSV file.");
            fclose($handle);
            return []; // Return empty if headers are not readable
        }

        // Create an associative array for header indices for easy lookup
        $headerIndices = array_flip($headers);

        // Loop through the CSV rows
        while (($row = fgetcsv($handle)) !== false) {
            $mappedRow = [];

            // Iterate through the headers and map the values
            foreach ($headers as $header) {
                $headerIndex = $headerIndices[$header];

                // Check if the field exists
                if (isset($row[$headerIndex])) {
                    $value = $row[$headerIndex];

                    // Check if the value is a string
                    if (is_string($value)) {
                        // Trim whitespace
                        $value = trim($value);
                        
                        // Attempt to convert to UTF-8
                        if (!mb_check_encoding($value, 'UTF-8')) {
                            // Log if the value is not valid UTF-8
                            error_log("Invalid UTF-8 detected: " . print_r($value, true));

                            // Attempt to detect encoding
                            $detectedEncoding = mb_detect_encoding($value, mb_detect_order(), true);
                            if ($detectedEncoding) {
                                // Convert to UTF-8 using detected encoding
                                $convertedValue = mb_convert_encoding($value, 'UTF-8', $detectedEncoding);
                                $value = $convertedValue;
                            } else {
                                // If encoding cannot be detected, set to null
                                $value = null;
                            }
                        }
                    } else {
                        // If it's not a string, set to null
                        $value = null;
                    }

                    // Map the value
                    $mappedRow[$header] = $value;
                } else {
                    $mappedRow[$header] = null; // Set to null if not present
                }
            }

            // Add the mapped row to the file data array
            $fileData[] = $mappedRow;
        }

        // Close the CSV file handle
        fclose($handle);
    } else {
        // Log error if file can't be opened
        error_log("Could not open file: " . $file);
    }

    return $fileData;
}

    
    public function deleteTemplate(Request $request){
        $find = Template::where('id',$request->id)->first();
        if($find){
            $find->delete();
            $response['message'] = 'Template Delted Successfully!';
            $response['success'] = true;
            $response['status_code'] = 200;
        }else{
            $response['message'] = 'Template not Found!';
            $response['success'] = false;
            $response['status_code'] = 400;
        }
        return response()->json($response);
    }
    public function getTemplateData(Request $request) {
        $templateId = $request->input('templateId');

        // Fetch the template from the database using the template ID
        $template = Template::find($templateId);

        if ($template) {
            // Assuming `mapping_template` contains your JSON mapping
            $mappingTemplate = json_decode($template->mapping_template, true);

            return response()->json([
                'templateData' => $mappingTemplate
            ]);
        } else {
            return response()->json([
                'error' => 'Template not found'
            ], 404);
        }
    }
    // public function processTemplate(Request $request)
    // {
    //     // Validate incoming request
    //     $request->validate([
    //         'path' => 'required|string',
    //         'source_id' => 'required|integer',
    //         'templateId' => 'required|integer',
    //     ]);
    //     // Get the template based on the source ID
    //     $template = Template::where('id',$request->templateId)->first();
    //     // Check if the template exists
    //     if (!$template) {
    //         return response()->json(['error' => 'Template not found'], 404);
    //     }
    //     // Get the mapping template
    //     $mappingTemplate = json_decode($template->mapping_template, true); // Decode if it's stored as JSON
    //     // Load the file data from the specified path
    //     $fileData = $this->loadFileData($request->path); // Create a method to load file data
    //     // Create leads based on the mapping
    //     $leads = [];
    //     foreach ($fileData as $row) {
    //         $leadData = [];
    //         foreach ($mappingTemplate as $dbColumn => $fileColumn) {
    //             if($fileColumn != null){
    //                 if ($fileColumn && isset($row[$fileColumn])) {
    //                     $leadData[$dbColumn] = $row[$fileColumn]; // Map file data to lead data
    //                 }
    //             }
               
    //         }
    //         $leads[] = $leadData; // Add the mapped lead data
    //     }
    //     $newLeadIds = array();
    //     // Save leads to the database
    //     foreach ($leads as $leadData) {
    //         $leadData['source_id'] = $request->source_id;
    //         if(isset($leadData['date'])){
    //             $leadData['date'] = date('Y-m-d H:i:s', strtotime($leadData['date'])); // Convert to YYYY-MM-DD HH:MM:SS format

    //         }
    //         $findLead = Lead::where('asin',$leadData['asin'])->where('source_id', $request->souce_id)->first();

    //         // Sanitize the price-related fields by removing any non-numeric characters except the decimal point.
            
    //         if (isset($leadData['sellPrice']) && $leadData['sellPrice'] !== "" && $leadData['sellPrice'] !== null) {
    //             $leadData['sell_price'] = $leadData['sellPrice'];
    //         } else {
    //             $leadData['sell_price'] = 0;
    //         }
            
    //         if (isset($leadData['netProfit']) && $leadData['netProfit'] !== "" && $leadData['netProfit'] !== null) {
    //             $leadData['net_profit'] = $leadData['netProfit'];
    //         } else {
    //             $leadData['net_profit'] = 0;
    //         }
    //         if (isset($leadData['cost']) && $leadData['cost'] !== "" && $leadData['cost'] !== null) {
    //             $leadData['cost'] = $leadData['cost'];
    //         } else {
    //             $leadData['cost'] = 0;
    //         }
            
    //         $priceFields = ['cost', 'sell_price', 'net_profit'];
    //         foreach ($priceFields as $field) {
    //             if (isset($leadData[$field])) {
    //                 $leadData[$field] = preg_replace('/[^\d.]/', '', $leadData[$field]);
    //             }
    //         }
    //         if(isset($leadData['roi'])){
    //             $leadData['roi'] = str_replace('%', '', $leadData['roi']);
    //         }
           
    //         $leadData['sell_price']  = $leadData['sell_price'] ==""?0:$leadData['sell_price'];
    //         if(auth()->user()){
    //             $leadData['created_by'] = auth()->user()->id;
    //         }
            

    //         if( $findLead){
    //             $findLead->update($leadData);
    //             $newLeadIds[] = $findLead->id;
                
    //         }else{
    //             $newLead = Lead::create($leadData);
    //             $newLeadIds[] = $newLead->id; // Save the newly created lead ID
    //         }
    //     }
    //      // Store the new lead IDs in a temporary session or database
    //     $batchId = uniqid(); // Generate a unique ID for this batch
    //     session()->put('lead_batch_' . $batchId, $newLeadIds); // Store the lead IDs in the session
    //     return response()->json([
    //         'success' => 'Leads processed successfully',
    //         'newLeadIds' => sizeof($newLeadIds), // Return newly created lead IDs
    //         'batchId' => $batchId,
    //     ], 200);
    // }
    public function processTemplate(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'path' => 'required|string',
            'source_id' => 'required|integer',
            'templateId' => 'required|integer',
        ]);
    
        // Get the template
        $template = Template::find($request->templateId);
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
    
        $mappingTemplate = json_decode($template->mapping_template, true);
        if (!is_array($mappingTemplate) || empty($mappingTemplate)) {
            return response()->json(['error' => 'Invalid mapping template'], 400);
        }
    
        try {
            $fileData = $this->loadFileData($request->path);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load file data: ' . $e->getMessage()], 400);
        }
    
        $leads = [];
        foreach ($fileData as $row) {
            $leadData = [];
            foreach ($mappingTemplate as $dbColumn => $fileColumn) {
                if (!empty($fileColumn) && isset($row[$fileColumn])) {
                    $leadData[$dbColumn] = $row[$fileColumn];
                }
            }
            $leads[] = $leadData;
        }
    
        $newLeadIds = [];
        $existingLeads = Lead::whereIn('asin', array_column($leads, 'asin'))
            ->where('source_id', $request->sourceid)
            ->get()
            ->keyBy('asin');
    
        foreach ($leads as $leadData) {
            $leadData['source_id'] = $request->source_id;
                if (isset($leadData['date'])) {
                    $leadData['date'] = date('Y-m-d H:i:s', strtotime($leadData['date'])); // Convert to YYYY-MM-DD HH:MM:SS format
                } else {
                    $leadData['date'] = date('Y-m-d H:i:s'); // Set current time if date is not set
                } 
                $findLead = Lead::where('asin',$leadData['asin'])->where('source_id', $request->souce_id)->first();
    
                // Sanitize the price-related fields by removing any non-numeric characters except the decimal point.
                
                if (isset($leadData['sellPrice']) && $leadData['sellPrice'] !== "" && $leadData['sellPrice'] !== null) {
                    $leadData['sell_price'] = $leadData['sellPrice'];
                } else {
                    $leadData['sell_price'] = 0;
                }
                
                if (isset($leadData['netProfit']) && $leadData['netProfit'] !== "" && $leadData['netProfit'] !== null) {
                    $leadData['net_profit'] = $leadData['netProfit'];
                } else {
                    $leadData['net_profit'] = 0;
                }
                if (isset($leadData['cost']) && $leadData['cost'] !== "" && $leadData['cost'] !== null) {
                    $leadData['cost'] = $leadData['cost'];
                } else {
                    $leadData['cost'] = 0;
                }
                
                $priceFields = ['cost', 'sell_price', 'net_profit'];
                foreach ($priceFields as $field) {
                    if (isset($leadData[$field])) {
                        $leadData[$field] = preg_replace('/[^\d.]/', '', $leadData[$field]);
                    }
                }
                if(isset($leadData['roi'])){
                    $leadData['roi'] = str_replace('%', '', $leadData['roi']);
                }
               
                $leadData['sell_price']  = $leadData['sell_price'] ==""?0:$leadData['sell_price'];
                if(auth()->user()){
                    $leadData['created_by'] = auth()->user()->id;
                }
    
                if (!empty($leadData['asin'])) {
                    if (isset($existingLeads[$leadData['asin']])) {
                        $existingLeads[$leadData['asin']]->update($leadData);
                        $newLeadIds[] = $existingLeads[$leadData['asin']]->id;
                    } else {
                        $newLead = Lead::create($leadData);
                        $newLeadIds[] = $newLead->id;
                    }
                }
        }
    
        $batchId = uniqid();
        session()->put('lead_batch_' . $batchId, $newLeadIds);
    
        return response()->json([
            'success' => 'Leads processed successfully',
            'newLeadIds' => count($newLeadIds),
            'batchId' => $batchId,
        ], 200);
    }

   private function loadFileData($path, $limit = null){
    // This example assumes the file is a CSV. Adjust as necessary.
    $fileData = [];
    
    // Check if the file exists before attempting to open it
    if (!file_exists($path)) {
        throw new Exception("File not found: " . $path);
    }
    
    if (($handle = fopen($path, 'r')) !== false) {
        // Get headers from the first row
        $headers = fgetcsv($handle);
        
        if ($headers === false) {
            fclose($handle);
            throw new Exception("Unable to read headers from the CSV file.");
        }

        // Initialize record count
        $recordCount = 0;
        while (($data = fgetcsv($handle)) !== false) {
            // Combine headers with data rows
             // Ensure the row has the same number of columns as headers
            $data = array_pad($data, count($headers), null); // Pad missing data with null

            // Now combine headers and data
            if (count($headers) == count($data)) {
                $rowData = array_combine($headers, $data);
                // Process the $rowData
            } else {
                continue; 
                // Handle error or log issue
                // echo "Error: Mismatched row length.";
            }
            
            // Optional: Clean up and encode string values
            foreach ($rowData as $key => $value) {
                if (is_string($value)) {
                    $value = trim($value);
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $detectedEncoding = mb_detect_encoding($value, mb_detect_order(), true);
                        if ($detectedEncoding) {
                            $value = mb_convert_encoding($value, 'UTF-8', $detectedEncoding);
                        } else {
                            $value = null; // Set to null if encoding cannot be detected
                        }
                    }
                } else {
                    $value = null; // Set to null if not a string
                }
                $rowData[$key] = $value; // Update cleaned value
            }

            // Add row data to the fileData array
            $fileData[] = $rowData;

            // Increment the record count and check against limit
            $recordCount++;
        }
        
        fclose($handle); // Close the file
    } else {
        throw new Exception("Could not open file: " . $path);
    }
    
    return $fileData;
}



    
}
