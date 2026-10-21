<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        return view('admin.users.index', [
            'users' => $query->paginate(10)->withQueryString(),
            'roles' => User::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => User::ROLE_MURID]),
            'roles' => User::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'role.in' => 'Role tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', "Pengguna \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => User::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = collect($validated)->except('password')->all();
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Cegah super admin menghapus role-nya sendiri
        if ($user->is($request->user()) && $validated['role'] !== User::ROLE_SUPER_ADMIN) {
            return back()->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "Pengguna \"{$user->name}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Pengguna \"{$name}\" dihapus.");
    }
}
