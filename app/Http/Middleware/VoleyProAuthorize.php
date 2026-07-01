<?php

namespace App\Http\Middleware;

use App\Support\VoleyProAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VoleyProAuthorize
{
    public function handle(Request $request, Closure $next, string $permission, string $action = 'puede_ver'): Response
    {
        VoleyProAccess::authorize($request, $permission, $action);

        return $next($request);
    }
}
