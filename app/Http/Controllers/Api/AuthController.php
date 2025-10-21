<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Auth\AuthenticationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Constructor
     *
     * @param AuthenticationServiceInterface $authService The authentication service
     */
    public function __construct(
        private AuthenticationServiceInterface $authService
    ) {}

    /**
     * Register a new user
     *
     * @param RegisterRequest $request The validated registration request
     * @return JsonResponse The user resource response
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request);
        return $this->resourceResponse(
            UserResource::make($result['user']),
            'User registered successfully'
        );
    }

    /**
     * Login a user
     *
     * @param LoginRequest $request The validated login request
     * @return JsonResponse The user resource or error response
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return $this->resourceResponse(
            UserResource::make($result['user']),
            'Login successful'
        );
    }

    /**
     * Logout a user
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The logout result response
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->logout($request->user());

        return $this->successResponse(
            null,
            'Logout successful'
        );
    }
}
