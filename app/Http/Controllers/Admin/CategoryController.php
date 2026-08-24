<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    protected function validationRules(Category $category = null): array
    {
        return [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120|unique:categories,slug'.($category ? ','.$category->id : ''),
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|in:'.implode(',', Category::ICONS),
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules());
        $data = $request->only(['name', 'description', 'icon', 'sort_order']);
        $data['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $data['icon'] = $data['icon'] ?? 'newspaper';
        $data['is_active'] = $request->boolean('is_active', true);
        if (empty($data['sort_order'])) $data['sort_order'] = Category::max('sort_order') + 1;
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate($this->validationRules($category));
        $data = $request->only(['name', 'description', 'icon', 'sort_order']);
        $data['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active');
        if (empty($data['icon'])) $data['icon'] = 'newspaper';
        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Cannot delete a category that has posts. Move or reassign its posts first.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($request->order as $idx => $id) {
            Category::where('id', $id)->update(['sort_order' => $idx]);
        }
        return response()->json(['status' => 'ok']);
    }
}
