<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::with('category')->latest();

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$q}%")
                ->orWhere('instructor', 'like', "%{$q}%"));
        }

        if ($request->filled('kategori')) {
            $query->whereHas('category', fn ($sub) => $sub->where('slug', $request->string('kategori')));
        }

        return view('admin.courses.index', [
            'courses' => $query->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.form', [
            'course' => new Course(['level' => 'Pemula', 'is_featured' => false]),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $slug = Str::slug($data['title']);
        if (Course::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(5));
        }
        $data['slug'] = $slug;

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', "Kursus \"{$data['title']}\" berhasil ditambahkan.");
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.form', [
            'course' => $course,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $this->validated($request);

        if ($course->title !== $data['title']) {
            $slug = Str::slug($data['title']);
            if (Course::where('slug', $slug)->where('id', '!=', $course->id)->exists()) {
                $slug .= '-'.Str::lower(Str::random(5));
            }
            $data['slug'] = $slug;
        }

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('success', "Kursus \"{$course->title}\" berhasil diperbarui.");
    }

    public function destroy(Course $course): RedirectResponse
    {
        $title = $course->title;
        $course->delete();

        return back()->with('success', "Kursus \"{$title}\" dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructor' => ['nullable', 'string', 'max:255'],
            'level' => ['required', Rule::in(['Pemula', 'Menengah', 'Lanjutan'])],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0', 'lte:price'],
            'duration_hours' => ['required', 'integer', 'min:0'],
            'lessons' => ['required', 'integer', 'min:0'],
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'students' => ['required', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'in:on'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_url' => ['nullable', 'url', 'max:600'],
        ], [
            'discount_price.lte' => 'Harga diskon tidak boleh lebih besar dari harga asli.',
            'image.image' => 'Berkas gambar kursus tidak valid.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'image_url.url' => 'URL gambar tidak valid.',
        ]);

        unset($data['image'], $data['image_url']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('courses', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->string('image_url')->toString();
        }

        return $data;
    }
}
