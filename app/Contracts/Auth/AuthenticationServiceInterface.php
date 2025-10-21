<?php

namespace App\Contracts\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

interface AuthenticationServiceInterface
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return array
     */
    public function register(RegisterRequest $request): array;

    /**
     * Login a user
     *
     * @param LoginRequest $request
     * @return array|JsonResponse
     */
    public function login(LoginRequest $request): array|JsonResponse;

    /**
     * Logout a user
     *
     * @param User $user
     * @return array
     */
    public function logout(User $user): array;
}