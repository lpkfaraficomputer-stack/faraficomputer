<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $query = Order::query();

        return view('admin.dashboard', [
            'stats' => [
                'revenue' => (clone $query)->sum('total'),
                'orders' => Order::count(),
                'pending' => Order::where('status', 'menunggu_pembayaran')->count(),
                'courses' => Course::count(),
                'categories' => Category::count(),
                'users' => User::count(),
                'students' => User::where('role', 'murid')->count(),
                'service_requests' => ServiceRequest::count(),
                'service_new' => ServiceRequest::where('status', 'baru')->count(),
            ],
            'recentOrders' => Order::latest()->take(6)->get(),
            'recentServices' => ServiceRequest::latest()->take(6)->get(),
            'popularCourses' => Course::with('category')
                ->withCount('items')
                ->orderByDesc('items_count')
                ->take(5)
                ->get(),
            'roleCounts' => User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }
}
