<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! in_array($request->route()?->getName(), ['profile', 'logout'], true)) {
            return redirect()
                ->route('profile')
                ->with('status', 'Please change your password before continuing.');
        }

        return $next($request);
    }
}
