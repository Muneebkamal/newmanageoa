<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $employees = User::with('source')
        ->whereNotNull('sync_lead_url')
        ->get();
        //Get today's date
        $start = Carbon::yesterday('America/New_York')->setTime(8, 0); // Yesterday 8 AM EST
        $end = Carbon::today('America/New_York')->setTime(8, 0);       // Today 8 AM EST
        $leads = Lead::whereBetween('created_at', [$start, $end])->get()->groupBy('source_id');
       
        return view('dashboard',compact('employees','leads'));
    }
}
