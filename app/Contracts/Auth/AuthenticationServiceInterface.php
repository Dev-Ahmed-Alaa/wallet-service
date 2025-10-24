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
    public function register(RegisterRequest $request): User;

    /**
     * Login a user
     *
     * @param LoginRequest $request
     * @return User|JsonResponse
     */
    public function login(LoginRequest $request): User|JsonResponse;

    /**
     * Logout a user
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void;

    /**
     * Generate a pin for a user
     * @param int $pin
     * @return void
     */
    public function generatePin(User $user, int $pin): void;
}
