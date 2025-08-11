<?php

namespace App\Http\Controllers;

use App\Models\CashBack;
use App\Models\Location;
use App\Models\RejectReason;
use App\Models\UserEmail;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SystemManagerController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!\Auth::user()->can('view_users_email')) {
            abort(403);
        }
        if (request()->ajax()) {
            $emails = UserEmail::select(['id', 'email']);
            return DataTables::of($emails)
            ->addColumn('action', function ($email) {
                return '
                    <button class="btn btn-warning btn-sm edit-email" data-id="'.$email->id.'" data-email="'.$email->email.'">Edit</button>
                    <button class="btn btn-danger btn-sm delete-email" data-id="'.$email->id.'">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
        }

        return view('system-manager.emails.index');
    }
    public function lcationsIndex()
    {
        if (!\Auth::user()->can('view_locations')) {
            abort(403);
        }
        return view('system-manager.locations.index');
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
        $request->validate(['email' => 'required|email|unique:user_emails,email']);
        try {
            $email = UserEmail::create([
                'email' => $request->email,
                'created_by' => auth()->id(),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Email added successfully',
                'email' => $email, // Return the created email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the email',
                'error' => $e->getMessage(), // Optional: include the error message
            ], 500);
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
        $request->validate(['email' => 'required|email|unique:user_emails,email,'.$id]);
        $email = UserEmail::findOrFail($id);
        $email->update([
            'email' => $request->email,
            'updated_by' => auth()->id(),
        ]);
        return response()->json(['success' => true, 'message' => 'Email updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $email = UserEmail::find($id);
    
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email not found!'], 404);
        }
    
        $email->delete();
    
        return response()->json(['success' => true, 'message' => 'Email deleted successfully!']);
    }
    //location fucntions
    public function locationStore(Request $request)
    {
        $request->validate([
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50',
        ]);

        $location = Location::create([
            'location' => $request->location,
            'type' => $request->type,
            'street_address'=>$request->street_address, 
            'apartment'=>$request->apartment, 
            'city'=>$request->city, 
            'state'=>$request->state, 
            'country'=>$request->country, 
            'zip'=>$request->zip,
            'created_by' => auth()->id(),
        ]);
        return response()->json(['success' => true, 'message' => 'Location added successfully!', 'data' => $location]);
    }
    public function locationUpdate(Request $request, $id)
    {
        $request->validate([
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50',
        ]);

        $location = Location::findOrFail($id);
        $location->update([
            'location' => $request->location,
            'type' => $request->type,
            'street_address'=>$request->street_address, 
            'apartment'=>$request->apartment, 
            'city'=>$request->city, 
            'state'=>$request->state, 
            'country'=>$request->country, 
            'zip'=>$request->zip,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Location updated successfully!', 'data' => $location]);
    }
    public function locationDestroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
        return response()->json(['success' => true, 'message' => 'Location deleted successfully!']);
    }
    public function list(Request $request)
    {
        return datatables()->of(Location::query())
        ->addColumn('action', function ($row) {
            return '<button class="btn btn-sm btn-primary edit-location" data-id="' . $row->id . '" 
            data-location="' . $row->location . '" 
            data-type="' . $row->type . '"
            data-street_address="' . $row->street_address . '"
            data-apartment="' . $row->apartment . '"
            data-city="' . $row->city . '"
            data-state="' . $row->state . '"
            data-country="' . $row->country . '"
            data-zip="' . $row->zip . '">Edit</button>
            <button class="btn btn-sm btn-danger delete-location" data-id="' . $row->id . '">Delete</button>';
        })
        ->make(true);
    }
    public function settingsIndex(){
        if (!\Auth::user()->can('view_settings')) {
            abort(403);
        }
        return view('system-manager.index');
    }
    public function storeCashBack(Request $request)
    {
        try {
            if ($request->has('cash_back_id') && $request->cash_back_id != null) {
                // Update existing cashback source
                $find = CashBack::find($request->cash_back_id);
                if ($find) {
                    $find->name = $request->name;
                    $find->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Cashback source updated successfully!'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cashback source not found!'
                    ]);
                }
            } else {
                // Create a new cashback source
                $newCash = new CashBack;
                $newCash->name = $request->name;
                $newCash->save();

                return response()->json([
                    'success' => true,
                    'message' => 'New cashback source added successfully!',
                    'data'=>$newCash,
                ]);
            }
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    public function getCashbacks()
    {
        $cashbacks = CashBack::select(['id', 'name', 'created_at']);
        return DataTables::of($cashbacks)
        ->addColumn('actions', function ($cashback) {
            return  '<a href="javascript:void(0);" onclick="editCashback('.$cashback->id.',\''.$cashback->name.'\')" class="btn btn-sm btn-primary">Edit</a>';
        })
        ->rawColumns(['actions']) // To render HTML instead of plain text
        ->make(true);
    }
     // DataTable JSON
    public function reasonData()
    {
        $reasons = RejectReason::orderBy('id', 'asc');

        return DataTables::of($reasons)
        ->addColumn('actions', function ($row) {
            return '<button class="btn btn-sm btn-primary" onclick="editRejectedReason('.$row->id.', \'' . e($row->reason) . '\')">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRejectedReason('.$row->id.')">Delete</button>';
        })
        ->rawColumns(['actions'])
        ->make(true);
    }

    // Store or Update
    public function storeReason(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        RejectReason::updateOrCreate(
            ['id' => $request->reason_id],
            ['reason' => $request->reason]
        );
        return response()->json(['message' => 'Saved successfully']);
    }

    // Delete
    public function destroyReason($id)
    {
        RejectReason::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
    public function Rasonlist()
    {
        return RejectReason::select('id', 'reason')->orderBy('id', 'desc')->get();
    }

}
