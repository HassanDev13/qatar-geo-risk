<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproval
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            \Illuminate\Support\Facades\Log::info("CheckUserApproval Log - Admin: " . ($user->is_admin ? 'Yes' : 'No') . ", Status: " . $user->status);
            if (!$user->is_admin && $user->status !== 'approved') {
                return redirect()->route('waiting');
            }
        }

        return $next($request);
    }
}
