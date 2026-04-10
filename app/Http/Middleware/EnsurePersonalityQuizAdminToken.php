<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonalityQuizAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.personality_quiz.admin_token', '');
        if ($expected === '') {
            abort(503, 'Personality quiz admin is not configured (PERSONALITY_QUIZ_ADMIN_TOKEN).');
        }

        $provided = (string) (
            $request->bearerToken()
            ?? $request->header('X-Personality-Quiz-Admin-Token')
            ?? $request->query('token')
            ?? ''
        );

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            abort(403, 'Invalid admin token.');
        }

        return $next($request);
    }
}
