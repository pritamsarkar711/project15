<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(){ $pages = Page::latest()->paginate(20); return view('admin.pages.index', compact('pages')); }
    public function create(){ return view('admin.pages.create'); }
    public function store(Request $request){
        $request->validate(['title'=>'required|string|max:255','slug'=>'nullable|string|unique:pages,slug','content'=>'required|string','status'=>'required|in:draft,published']);
        $data = $request->only(['title','content','status','meta_title','meta_description']);
        $data['slug'] = $request->slug?Str::slug($request->slug):Str::slug($request->title);
        Page::create($data);
        return redirect()->route('admin.pages.index')->with('success','Page created');
    }
    public function edit(Page $page){ return view('admin.pages.edit', compact('page')); }
    public function update(Request $request, Page $page){
        $request->validate(['title'=>'required|string|max:255','slug'=>'nullable|string|unique:pages,slug,'.$page->id,'content'=>'required|string','status'=>'required|in:draft,published']);
        $data = $request->only(['title','content','status','meta_title','meta_description']);
        $data['slug'] = $request->slug?Str::slug($request->slug):Str::slug($request->title);
        $page->update($data);
        return redirect()->route('admin.pages.index')->with('success','Page updated');
    }
    public function destroy(Page $page){ $page->delete(); return back()->with('success','Page deleted'); }
}
