<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request, ?string $slug = null): View
    {
        $categories = Category::withCount('courses')->orderBy('sort_order')->get();

        $query = Course::with('category');

        $category = null;
        if ($slug) {
            $category = Category::where('slug', $slug)->firstOrFail();
            $query->where('category_id', $category->id);
        } elseif ($request->filled('kategori')) {
            $category = Category::where('slug', $request->string('kategori'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('instructor', 'like', "%{$q}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->string('level'));
        }

        match ($request->input('sort')) {
            'harga_termurah' => $query->orderByRaw('COALESCE(discount_price, price) ASC'),
            'harga_termahal' => $query->orderByRaw('COALESCE(discount_price, price) DESC'),
            'populer' => $query->orderByDesc('students'),
            default => $query->latest(),
        };

        return view('courses.index', [
            'courses' => $query->paginate(9)->withQueryString(),
            'categories' => $categories,
            'activeCategory' => $category,
            'levels' => ['Pemula', 'Menengah', 'Lanjutan'],
        ]);
    }

    public function show(string $slug): View
    {
        $course = Course::with('category')->where('slug', $slug)->firstOrFail();

        $related = Course::with('category')
            ->where('category_id', $course->category_id)
            ->where('id', '!=', $course->id)
            ->take(3)
            ->get();

        return view('courses.show', [
            'course' => $course,
            'related' => $related,
        ]);
    }
}
