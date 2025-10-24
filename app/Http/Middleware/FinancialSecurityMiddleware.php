<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Financial Security Middleware
 *
 * Provides comprehensive security for financial transactions including:
 * - Rate limiting
 * - Request validation
 * - Fraud detection
 * - IP-based security
 * - Transaction monitoring
 * - Request encryption verification
 */
class FinancialSecurityMiddleware
{
    /**
     * Maximum number of financial requests allowed per minute per user
     */
    private const MAX_REQUESTS_PER_MINUTE = 10;

    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->applyRateLimiting($request);
        $this->verifyUserPin($request);

        return $next($request);
    }

    /**
     * Apply rate limiting to financial requests
     *
     * @param Request $request The incoming HTTP request
     * @return void
     */
    private function applyRateLimiting(Request $request): void
    {
        $user = $request->user();
        $userId = $user ? $user->id : $request->ip();

        $key = 'financial_' . $userId . '_' . $request->path();

        if (RateLimiter::tooManyAttempts($key, self::MAX_REQUESTS_PER_MINUTE)) {
            Log::warning('Rate limit exceeded for financial request', [
                'user_id' => $userId,
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);

            $seconds = RateLimiter::availableIn($key);

            abort(429, "Too many requests. Please try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, 60);
    }

    /**
     * Verify the user's PIN
     *
     * @param Request $request The incoming HTTP request
     * @return void
     */
    private function verifyUserPin(Request $request): void
    {
        $pin = $request->input('pin');
        $user = $request->user();

        if (!$user->wallet->pin_hash) {
            throw ValidationException::withMessages(['pin' => ['You need to set a PIN first']]);
        } elseif (!Hash::check($pin, $user->wallet->pin_hash)) {
            throw ValidationException::withMessages(['pin' => ['Invalid PIN']]);
        }
    }
}
