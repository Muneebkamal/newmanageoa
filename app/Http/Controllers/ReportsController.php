<?php

namespace App\Http\Controllers;

use App\Models\Buylist;
use App\Models\Lead;
use App\Models\LineItem;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //
    public function index()
    {
        if (!\Auth::user()->can('view_settings')) {
            abort(403);
        }
        $employees = User::where('role_id', '!=', 1)->get();
        return view('reports.index', get_defined_vars());
    }
    public function filterReport(Request $request){
       
$startDate = Carbon::parse($request->input('start_date'))->startOfDay();
$endDate = Carbon::parse($request->input('end_date'))->endOfDay();
$employeeIds = explode(',', $request->input('employee_id'));

$period = CarbonPeriod::create($startDate, $endDate);
$data = [];

// Get employee names
$employees = User::whereIn('id', $employeeIds)->get()->keyBy('id');

foreach ($employeeIds as $empId) {
    $employeeName = $employees[$empId]->name ?? 'Unknown';

    $employeeData = [
        'employee_id' => $empId,
        'employee_name' => $employeeName,
        'data' => []
    ];

    foreach ($period as $date) {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Leads
        $leadsCount = Lead::where('created_by', $empId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        // Buylists
        $buylistIds = Buylist::where(function ($query) use ($empId) {
        $query->orWhere('creatd_by', $empId);
        })->pluck('id');

        $buylistUnits = LineItem::whereIn('buylist_id', $buylistIds)
        ->whereBetween('created_at', [$startOfDay, $endOfDay])
        ->count();
        // Only add if either has value
        if ($leadsCount > 0 || $buylistUnits > 0) {
            $employeeData['data'][] = [
                'date' => $date->format('Y-m-d'),
                'leads' => $leadsCount,
                'buylist' => $buylistUnits
            ];
        }
    }

    // Only add if there’s at least one entry
    if (!empty($employeeData['data'])) {
        $data[] = $employeeData;
    }
}

return response()->json($data);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $employeeId = $request->input('employee_id');

        // Create a period from start to end date
        $period = CarbonPeriod::create($startDate, $endDate);
        $data = [];

        foreach ($period as $date) {
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
        
            $employeeIds = explode(',', $employeeId);

            // Leads count
            $leadsCount = Lead::when(count($employeeIds) > 1, function ($query) use ($employeeIds) {
                return $query->whereIn('created_by', $employeeIds);
            }, function ($query) use ($employeeId) {
                return $query->where('created_by', $employeeId);
            })
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

            // Buylist IDs (from employee_id or created_by)
            $buylists = Buylist::where(function ($query) use ($employeeIds, $employeeId) {
                if (count($employeeIds) > 1) {
                    $query->whereIn('employee_id', $employeeIds)
                            ->orWhereIn('creatd_by', $employeeIds);
                } else {
                    $query->where('employee_id', $employeeId)
                            ->orWhere('creatd_by', $employeeId);
                }
            })->pluck('id');

        // Line item count
        $buylistUnits = LineItem::whereIn('buylist_id', $buylists)
        ->whereBetween('created_at', [$startOfDay, $endOfDay])
        ->count();

            // ✅ Only push data if leads or buylist > 0
            if ($leadsCount > 0 || $buylistUnits > 0) {
                $data[] = [
                    'date' => $date->format('Y-m-d'),
                    'leads' => $leadsCount,
                    'buylist' => $buylistUnits,
                ];
            }
        }
        

        // return response()->json($data);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $startDate = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endDate =  Carbon::parse($endDate)->endOfDay()->toDateTimeString();
        $employeeId = $request->input('employee_id');

        $leadsCout = Lead::where('created_by', $employeeId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        $buylists = Buylist::where('employee_id', $employeeId)
        ->orWhere('creatd_by', $employeeId)
        ->get();
        $buylistUnits = 0;
        foreach ($buylists as $key => $value) {
            # code...
            $buylistUnits += LineItem::where('buylist_id',$value->id)->whereBetween('created_at', [$startDate, $endDate])->sum('unit_purchased');

        }
        $response = [
            'leads' => $leadsCout,
            'buylist' => $buylistUnits 
        ];

        return response()->json($response);
    }
}
