<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $reasons = ['General Inquiry','Advertising','Guest Post','Technical Support','Feedback','Report Issue','Partnership','Other'];
        return view('frontend.contact', compact('reasons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'reason' => 'required|string|max:100',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
        ]);
        ContactMessage::create($request->only(['name','email','reason','subject','message']));
        return back()->with('success','Thank you! Your message has been received. We will respond shortly.');
    }
}
