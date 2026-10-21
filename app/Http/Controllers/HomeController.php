<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Course::with('category')->where('is_featured', true)->latest()->take(4)->get();
        $popular = Course::with('category')->orderByDesc('students')->take(8)->get();

        // Gabungan: unggulan dulu, lalu isi dengan yang terpopuler (maks 8)
        $pilihan = $featured->concat(
            $popular->reject(fn ($c) => $featured->pluck('id')->contains($c->id))
        )->take(8)->values();

        return view('home', [
            'categories' => Category::withCount('courses')->orderBy('sort_order')->get(),
            'pilihan' => $pilihan,
        ]);
    }
}
