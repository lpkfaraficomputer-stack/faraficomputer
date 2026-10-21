<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Contoh pemakaian: ->middleware('role:super_admin,operator')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()
                ->route($user->role === 'murid' ? 'account.dashboard' : 'admin.dashboard')
                ->with('error', 'Anda tidak punya akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
