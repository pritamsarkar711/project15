<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::latest();
        if ($request->filled('filter')) {
            if ($request->filter === 'unread') $query->where('is_read', false);
            if ($request->filter === 'read') $query->where('is_read', true);
        }
        $messages = $query->paginate(20)->withQueryString();
        $unread = ContactMessage::where('is_read', false)->count();
        return view('admin.contacts.index', compact('messages', 'unread'));
    }

    public function show(ContactMessage $contact)
    {
        $contact->update(['is_read' => true]);
        return view('admin.contacts.show', compact('contact'));
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Message deleted');
    }

    public function markRead(ContactMessage $contact)
    {
        $contact->update(['is_read' => !$contact->is_read]);
        return back()->with('success', $contact->is_read ? 'Message marked as read' : 'Message marked as unread');
    }
}
