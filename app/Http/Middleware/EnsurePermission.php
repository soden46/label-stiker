<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        $allowed = collect($permissions)->contains(fn (string $permission) => $user?->canAccess($permission));

        abort_unless($allowed, 403, 'Anda tidak memiliki izin untuk halaman ini.');

        return $next($request);
    }
}
