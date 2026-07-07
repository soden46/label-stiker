<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserPortal
{
    public function handle(Request $request, Closure $next, string $portal): Response
    {
        abort_unless($request->user()?->is_active && $request->user()->portal === $portal, 403, 'Akun tidak memiliki akses ke portal ini.');

        return $next($request);
    }
}
