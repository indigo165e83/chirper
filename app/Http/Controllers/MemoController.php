<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreMemoRequest;
use App\Http\Requests\UpdateMemoRequest;

class MemoController extends Controller
{
    use AuthorizesRequests;

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
    public function store(StoreMemoRequest $request)
    {
        // 作成は対象が存在しないため所有判定は不要。未ログインは auth ミドルウェアが弾く
        $validated = $request->validated();
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
        $this->authorize('update', $memo);
        return view('memos.edit', ['memo' => $memo]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemoRequest $request, Memo $memo)
    {
        // 認可は UpdateMemoRequest::authorize() で行う（検証より先に 403 を返すため）
        $validated = $request->validated();
        $memo->update($validated);
        return redirect()->route('memos.index')->with('success', 'Your memo has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Memo $memo)
    {
        $this->authorize('delete', $memo);
        $memo->delete();
        return redirect()->route('memos.index')->with('success', 'Your memo has been deleted!');
    }
}
