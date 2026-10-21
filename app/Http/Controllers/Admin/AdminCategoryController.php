<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('courses')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        Category::create($data);

        return back()->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request);

        if ($category->name !== $data['name']) {
            $slug = Str::slug($data['name']);
            if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug .= '-'.Str::lower(Str::random(5));
            }
            $data['slug'] = $slug;
        }

        $category->update($data);

        return back()->with('success', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->with('error', "Kategori \"{$category->name}\" tidak bisa dihapus karena masih memiliki kursus.");
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', "Kategori \"{$name}\" dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['required', 'string', 'max:7'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
