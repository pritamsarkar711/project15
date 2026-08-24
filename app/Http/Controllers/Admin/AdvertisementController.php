<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public const POSITIONS = [
        'header'     => 'Header',
        'sidebar'    => 'Sidebar',
        'in_article' => 'In Article',
        'footer'     => 'Footer',
    ];

    public function index()
    {
        $ads = Advertisement::orderBy('sort_order')->get()->groupBy('position');
        return view('admin.ads.index', ['ads' => $ads, 'positions' => self::POSITIONS]);
    }

    protected function rules(): array
    {
        return [
            'title'    => 'required|string|max:255',
            'position' => 'required|in:'.implode(',', array_keys(self::POSITIONS)),
            'code'     => 'nullable|string|max:10000',
            'link'     => 'nullable|url|max:255',
            'is_active'=> 'nullable|boolean',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        $data = $request->only(['title', 'position', 'code', 'link']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = Advertisement::where('position', $data['position'])->max('sort_order') + 1;
        Advertisement::create($data);
        return back()->with('success', 'Ad created');
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $request->validate($this->rules());
        $data = $request->only(['title', 'position', 'code', 'link']);
        $data['is_active'] = $request->boolean('is_active');
        $advertisement->update($data);
        return back()->with('success', 'Ad updated');
    }

    public function destroy(Advertisement $advertisement)
    {
        $advertisement->delete();
        return back()->with('success', 'Ad deleted');
    }

    public function toggle(Advertisement $advertisement)
    {
        $advertisement->is_active = !$advertisement->is_active;
        $advertisement->save();
        return back()->with('success', 'Ad '.($advertisement->is_active ? 'activated' : 'deactivated'));
    }
}
