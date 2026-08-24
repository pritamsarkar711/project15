<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug',$slug)->where('status','published')->firstOrFail();
        return view('frontend.pages.show', compact('page'));
    }
    public function about()
    {
        $page = Page::where('slug','about')->first();
        return view('frontend.about', compact('page'));
    }
    public function privacy(){ $page = Page::where('slug','privacy-policy')->firstOrFail(); return view('frontend.pages.show', compact('page')); }
    public function terms(){ $page = Page::where('slug','terms-conditions')->firstOrFail(); return view('frontend.pages.show', compact('page')); }
    public function cookie(){ $page = Page::where('slug','cookie-policy')->firstOrFail(); return view('frontend.pages.show', compact('page')); }
    public function editorial(){ $page = Page::where('slug','editorial-policy')->firstOrFail(); return view('frontend.pages.show', compact('page')); }
}
