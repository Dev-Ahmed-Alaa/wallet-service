<?php

namespace App\Services;

use App\Contracts\Auth\AuthenticationServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
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
     * @return User
     */
    public function register(RegisterRequest $request): User
    {
        $result = DB::transaction(function () use ($request) {
            $user = User::create($request->validated());

            $this->walletService->ensureUserWallet($user);

            $token = $user->createToken('default')->plainTextToken;
            $user->token = $token;

            return $user;
        });

        return $result;
    }

    /**
     * Login a user
     *
     * @param LoginRequest $request
     * @return User
     */
    public function login(LoginRequest $request): User|JsonResponse
    {
        $user = User::where('email', $request->validated()['email'])->first();

        if (!$user || !Hash::check($request->validated()['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken('default')->plainTextToken;
        $user->token = $token;

        return $user;
    }

    /**
     * Logout a user
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Generate a pin
     *
     * @param int $pin
     * @return void
     */
    public function generatePin(User $user, int $pin): void
    {
        $user
            ->wallet()
            ->whereNull('pin_hash')
            ->lockForUpdate()
            ->update([
                'pin_hash' => Hash::make($pin),
            ]);
    }
}
