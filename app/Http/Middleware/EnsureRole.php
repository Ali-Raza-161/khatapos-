<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
class EnsureRole {
    public function handle(Request $request, Closure $next, ...$roles) {
        $user = Auth::user();
        if (!$user || !in_array($user->role, $roles)) abort(403, 'You do not have permission for this page.');
        return $next($request);
    }
}
