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
        // published_posts_count powers the "live on site" indicator: a category
        // is only visible to visitors when it is active AND has ≥1 published post.
        $categories = Category::withCount('posts')->withCount(['posts as published_posts_count' => fn ($q) => $q->published()])->orderBy('sort_order')->get();
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
        $data['slug'] = $this->uniqueCategorySlug($request->slug ? Str::slug($request->slug) : Str::slug($request->name));
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
        $data['slug'] = $this->uniqueCategorySlug(
            $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            $category->id
        );
        $data['is_active'] = $request->boolean('is_active');
        if (empty($data['icon'])) $data['icon'] = 'newspaper';
        // sort_order is NOT NULL in the database: an emptied field would
        // otherwise crash the UPDATE under MySQL strict mode ("Column
        // 'sort_order' cannot be null"). Keep the previous value instead.
        if (!isset($data['sort_order']) || $data['sort_order'] === '') {
            $data['sort_order'] = $category->sort_order ?? 0;
        }
        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated');
    }

    /**
     * Generate a guaranteed-unique category slug.
     *
     * The old code validated uniqueness against the RAW input but stored a
     * Str::slug()'d version — so "Tech News" passed validation yet collided
     * with an existing "tech-news" row, throwing a duplicate-key 500. Slugs
     * are now made unique AFTER slugification, with a -2/-3… suffix, and an
     * empty result (non-Latin names) falls back to a random slug.
     */
    protected function uniqueCategorySlug(string $base, ?int $ignoreId = null): string
    {
        if ($base === '') {
            $base = 'category-'.strtolower(Str::random(6));
        }
        $base = mb_substr($base, 0, 110);
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Cannot delete a category that has posts. Move or reassign its posts first.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    /**
     * Quick enable / disable toggle from the categories list.
     * Disabled categories disappear from the public site immediately.
     */
    public function toggle(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();
        return back()->with('success', $category->is_active
            ? "\"{$category->name}\" is now enabled" . ($category->posts()->where('status', 'published')->exists() ? ' and visible on the site.' : '. It becomes visible once it has at least one published post.')
            : "\"{$category->name}\" is now hidden from the site.");
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
