<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;


class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = Expense::query();
            return DataTables::of($expenses)
            ->addColumn('name', function ($expens) {
                return $expens->category ? $expens->category->name : 'N/A';
            })
            ->addColumn('starting_date', function ($expens) {
                return $expens->starting_date 
                ? Carbon::parse($expens->starting_date)->format('Y-m-d') 
                : null;
            })
            ->addColumn('amount', function ($expens) {
                return  '$'.$expens->amount;
            })
            ->addColumn('description', function ($expens) {
                return $expens->description;
            })
            
            ->addColumn('type', function ($expens) {
                return ucfirst($expens->type); // Capitalize the type (optional)
            })
            ->addColumn('actions', function ($expens) {
                return '
                    <button 
                        class="btn btn-sm btn-warning edit-expense" 
                        data-id="' . $expens->id . '" 
                        data-category-id="' . $expens->category_id . '" 
                        data-name="' . addslashes($expens->category ? $expens->category->name : 'N/A') . '" 
                        data-amount="' . $expens->amount . '" 
                        data-starting-date="' . ($expens->starting_date ? Carbon::parse($expens->starting_date)->format('Y-m-d')  : '') . '" 
                        data-type="' . $expens->type . '" 
                        data-description="' . addslashes($expens->description) . '"
                        onclick="editExpense(this)"
                    >
                        Edit
                    </button>
                    <button 
                        class="btn btn-sm btn-danger delete-expense" 
                        onclick="deleteExpense(' . $expens->id . ')" 
                        data-id="' . $expens->id . '"
                    >
                        Delete
                    </button>
                ';
            })            
            ->rawColumns(['category_id','actions'])
            ->make(true);
        }
        return view('expense-managment.expenses');
    }
    public function catIndex(Request $request){
        if ($request->ajax()) {
            $categories = Category::query();
            return DataTables::of($categories)
                ->addColumn('actions', function ($category) {
                    return '
                        <button class="btn btn-sm btn-warning edit-category"                         onclick="editCategory(' . $category->id . ', \'' . addslashes($category->name) . '\')" data-id="'.$category->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger delete-category" onclick="deleteCategory('.$category->id.')" data-id="'.$category->id.'">Delete</button>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    
        return view('expense-managment.categories');
    }
    public function catStore(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Check uniqueness, ignoring the current category if updating
                Rule::unique('categories', 'name')->ignore($request->id)
            ],
        ]);
        if(isset($request->id) && $request->id != null){
            Category::where('id',$request->id)
            ->update([
                'name' => $request->name,
            ]);
            return response()->json(['message' => 'Category updated successfully!','success'=>true], 201);
        }else{
            $category =Category::create([
                'name' => $request->name,
            ]);
            return response()->json(['message' => 'Category added successfully!','success'=>true,'data'=>$category], 201);
        }
    }
    public function catDestroy($id)
    {
        $findCate = Category::where('id',$id)->first();
        if($findCate){
            $findCate->delete();
            return response()->json(['message' => 'Category deleted successfully!','success'=>true], 201);
        }else{
            return response()->json(['message' => 'Category something wrong successfully!'], 201);
        }
    }
    public function categoriesList(Request $request)
    {
        $categories = Category::latest('created_at')
        ->get();
        return response()->json($categories);
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
        $validated = $request->validate([
            'id' => 'nullable|exists:expenses,id', // Check if ID exists in the `expenses` table
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'starting_date' => 'required|date',
            'type' => 'required|in:daily,weekly,monthly,yearly',
            'description' => 'nullable|string|max:500',
        ]);

        if (!empty($validated['id'])) {
            // Update existing expense
            $expense = Expense::find($validated['id']);
            $expense->update([
                'category_id' => $validated['category_id'],
                'amount' => $validated['amount'],
                'starting_date' => $validated['starting_date'],
                'type' => $validated['type'],
                'description' => $validated['description'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully.',
                'data' => $expense,
            ]);
        } else {
            // Create new expense
            $expense = Expense::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully.',
                'data' => $expense,
            ]);
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
}
