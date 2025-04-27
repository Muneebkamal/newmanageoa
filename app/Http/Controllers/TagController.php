<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tags = Tag::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $tags
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tag = new Tag;
        $tag->name = $request->name;
        $tag->color = "primary";
        $tag->is_enable = 0;
        $tag->save();

        return response()->json([
            'message' => 'Tag Added successfully!'
        ]);
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
        $tag = Tag::where('id', $id)->first();
        $tag->name = $request->name;
        $tag->color = $request->color;
        $tag->save();

        return response()->json([
            'message' => 'Tag Updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tag = Tag::where('id', $id)->first();
        $tag->delete();

        return response()->json([
            'message' => 'Tag Deleted successfully!'
        ]);
    }

    public function tagsGet(Request $request)
    {
        $query = $request->get('query', '');

        // Fetch all tags if no search query, else perform search
        $tags = Tag::where('name', 'LIKE', "%{$query}%")->get();

        return response()->json($tags);
    }

    public function tagsCheck(Request $request, $id)
    {
        $tag = Tag::where('id', $id)->first();
        $tag->is_enable = $request->enable;
        $tag->save();

        return response()->json([
            'message' => 'Tag checked successfully!'
        ]);
    }

    public function searchTags(Request $request)
    {
        $query = $request->get('query', '');

        // Fetch all tags if no search query, else perform search
        $tags = Tag::where('name', 'LIKE', "%{$query}%")->get();

        return response()->json($tags);
    }

    public function leadTagsStore(Request $request, $id) 
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }


        $tagsString = $request->input('tags');
        $tagsArray = $tagsString;
        $lead->tags = $tagsArray;
        $lead->save();
        $tagsArray = explode(',',$tagsArray);
        $newTags = Tag::whereIn('id', $tagsArray)->get();

    
        return response()->json([
            'message' => 'Tags added to lead successfully!',
            'newTags' => $newTags
        ]);
    }

    public function tagsUncheck()
    {
        $tags = Tag::all();
        foreach( $tags as $tag ) {
            $tag->is_enable = 0;
            $tag->save();
        }
        return response()->json([
            'message' => 'Tags Unchecked successfully!'
        ]);
    }
}
