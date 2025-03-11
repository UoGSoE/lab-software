<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AcademicSession;
use Symfony\Component\HttpFoundation\Response;

class SetAcademicSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->missing('academic_session_id')) {
            session()->put('academic_session_id', AcademicSession::getDefault()?->id ?? 0);
        }
        return $next($request);
    }
}
