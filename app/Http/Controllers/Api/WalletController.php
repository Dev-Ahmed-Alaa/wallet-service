<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Wallet\WalletServiceInterface;
use App\Utils\MoneyUtil;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\DepositRequest;
use App\Http\Requests\Wallet\TransferRequest;
use App\Http\Requests\Wallet\WithdrawRequest;
use App\Http\Resources\LedgerEntryResource;
use App\Http\Resources\WalletResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Repositories\LedgerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use ApiResponseTrait;

    /**
     * Constructor
     *
     * @param WalletServiceInterface $walletService The wallet service
     * @param LedgerRepository $ledgerRepository The ledger repository
     */
    public function __construct(
        private WalletServiceInterface $walletService,
        private LedgerRepository $ledgerRepository
    ) {}

    /**
     * Display the user's wallet information
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The wallet resource response
     */
    public function wallet(Request $request): JsonResponse
    {
        $wallet = $this->walletService->ensureUserWallet($request->user());

        return $this->resourceResponse(new WalletResource($wallet), 'Wallet retrieved successfully');
    }

    /**
     * Display the user's transaction history
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The ledger entries collection response
     */
    public function ledger(Request $request): JsonResponse
    {
        $wallet = $this->walletService->ensureUserWallet($request->user());
        $entries = $this->ledgerRepository->getEntriesForWallet($wallet);

        return $this->resourceResponse(LedgerEntryResource::collection($entries), 'Transaction history retrieved successfully');
    }

    /**
     * Deposit funds into the user's wallet
     *
     * @param DepositRequest $request The validated deposit request
     * @return JsonResponse The deposit result response
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        $amount = new MoneyUtil($request->validated()['amount']);
        $result = $this->walletService->deposit(
            $request->user(),
            $amount,
            $request->validated()['idempotency_key'] ?? null
        );

        return $this->successResponse($result, 'Funds deposited successfully', 201);
    }

    /**
     * Withdraw funds from the user's wallet
     *
     * @param WithdrawRequest $request The validated withdraw request
     * @return JsonResponse The withdrawal result response
     */
    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $amount = new MoneyUtil($request->validated()['amount']);
        $result = $this->walletService->withdraw(
            $request->user(),
            $amount,
            $request->validated()['idempotency_key'] ?? null
        );

        return $this->successResponse($result, 'Funds withdrawn successfully');
    }

    /**
     * Transfer funds from the user's wallet to another user
     *
     * @param TransferRequest $request The validated transfer request
     * @return JsonResponse The transfer result response
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $receiver = User::findOrFail($request->validated()['to_user_id']);
        $amount = new MoneyUtil($request->validated()['amount']);

        $result = $this->walletService->transfer(
            $request->user(),
            $receiver,
            $amount,
            $request->validated()['idempotency_key'] ?? null
        );

        return $this->successResponse($result->toArray(), 'Funds transferred successfully');
    }
}
