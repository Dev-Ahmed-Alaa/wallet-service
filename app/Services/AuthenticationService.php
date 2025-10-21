<?php

namespace App\Services;

use App\Contracts\Auth\AuthenticationServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Constructor
     *
     * @param WalletService $walletService The wallet service
     */
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return array
     */
    public function register(RegisterRequest $request): array
    {
        $result = DB::transaction(function () use ($request) {
            $user = User::create($request->validated());

            $this->walletService->ensureUserWallet($user);

            $token = $user->createToken('default')->plainTextToken;
            $user->token = $token;

            return [
                'user' => $user,
                'status' => 'success',
                'message' => 'User created successfully',
            ];
        });

        return $result;
    }

    /**
     * Login a user
     *
     * @param LoginRequest $request
     * @return array|JsonResponse
     */
    public function login(LoginRequest $request): array|JsonResponse
    {
        $user = User::where('email', $request->validated()['email'])->first();

        if (!$user || !Hash::check($request->validated()['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken('default')->plainTextToken;
        $user->token = $token;

        return [
            'user' => $user,
            'status' => 'success',
            'message' => 'User logged in successfully',
        ];
    }

    /**
     * Logout a user
     *
     * @param User $user
     * @return array
     */
    public function logout(User $user): array
    {
        $user->currentAccessToken()->delete();

        return [
            'status' => 'success',
            'message' => 'User logged out',
        ];
    }
}
