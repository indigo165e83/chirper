<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;

class MemoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $memos = auth()->user()->memos()->latest()->take(50)->get();
    
        return view('memos.index', ['memos' => $memos]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('memos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:1000',
            'is_draft' => 'boolean',
        ],[
            'title.required' => 'Please enter a title for your memo!',
            'title.max' => 'Titles must be 100 characters or less.',
            'body.required' => 'Please write something for your memo!',
            'body.max' => 'Memos must be 1000 characters or less.',
        ]);

        // Create the memo
        auth()->user()->memos()->create($validated);

        return redirect()->route('memos.index')->with('success', 'Your memo has been created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Memo $memo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Memo $memo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Memo $memo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Memo $memo)
    {
        //
    }
}
