<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function index(){
        $header = NavigationItem::position('header')->orderBy('sort_order')->get();
        $mobile = NavigationItem::position('mobile')->orderBy('sort_order')->get();
        $footer = NavigationItem::position('footer')->orderBy('sort_order')->get();
        return view('admin.navigation.index', compact('header','mobile','footer'));
    }
    public function store(Request $request){
        $request->validate(['label'=>'required|string|max:100','url'=>'required|string|max:255','position'=>'required|in:header,mobile,footer']);
        NavigationItem::create([
            'label'=>$request->label,
            'url'=>$request->url,
            'position'=>$request->position,
            'sort_order'=> NavigationItem::where('position',$request->position)->max('sort_order')+1,
            'is_active'=>true,
        ]);
        return back()->with('success','Navigation item added');
    }
    public function update(Request $request, NavigationItem $navigation){
        $request->validate(['label'=>'required|string|max:100','url'=>'required|string|max:255','is_active'=>'nullable|boolean']);
        $navigation->update(['label'=>$request->label,'url'=>$request->url,'is_active'=>$request->boolean('is_active')]);
        return back()->with('success','Updated');
    }
    public function destroy(NavigationItem $navigation){ $navigation->delete(); return back()->with('success','Deleted'); }
    public function reorder(Request $request){
        $request->validate(['position'=>'required|in:header,mobile,footer','order'=>'required|array','order.*'=>'integer']);
        foreach($request->order as $idx=>$id){
            NavigationItem::where('id',$id)->where('position',$request->position)->update(['sort_order'=>$idx]);
        }
        return response()->json(['status'=>'ok']);
    }
}
