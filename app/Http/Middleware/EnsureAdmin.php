<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Halaman Manajemen User/Role & Audit Log hanya untuk admin --
// menu-nya juga disembunyikan di sidebar untuk role lain, tapi
// route-nya tetap harus ditutup di sisi server (jangan andalkan UI saja).
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless(
            $user?->isAdmin(),
            403,
            'Halaman ini khusus Administrator.'
        );

        if ($request->routeIs('admin.users.*') && !$user->canAccessModule('user_mgmt')) {
            abort(403, 'Role Anda tidak memiliki akses ke Manajemen User.');
        }

        if ($request->routeIs('admin.audit-log.*') && !$user->canAccessModule('audit_log')) {
            abort(403, 'Role Anda tidak memiliki akses ke Audit Log.');
        }

        return $next($request);
    }
}
