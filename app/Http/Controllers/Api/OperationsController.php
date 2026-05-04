<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRmbSettlement;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OperationsController extends Controller
{
    /**
     * Get User Dashboard Data
     * GET /api/dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return $user;
        // 1. Ensure wallets exist for NGN and CNY
        $currencies = ['NGN', 'CNY'];
        foreach ($currencies as $currency) {
            Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => $currency],
                ['balance' => 0] // Default to 0 Kobo/Fen
            );
        }

        // 2. Load fresh wallets and the latest 10 transactions
        $user->load(['wallets' => function ($query) {
            $query->orderBy('currency', 'asc');
        }]);

        // 3. Fetch latest transactions across all user wallets
        $transactions = Transaction::whereIn('wallet_id', $user->wallets->pluck('id'))
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'member_since' => $user->created_at->format('Y-m-d'),
                ],
                'wallets' => $user->wallets->map(function ($wallet) {
                    return [
                        'id' => $wallet->id,
                        'currency' => $wallet->currency,
                        // Balance is stored as BigInt (e.g. 10000 = 100.00)
                        'balance' => $wallet->balance / 100,
                        'formatted_balance' => number_format($wallet->balance / 100, 2),
                    ];
                }),
                'recent_transactions' => $transactions->map(function ($tx) {
                    return [
                        'reference' => $tx->reference,
                        'amount' => $tx->amount / 100,
                        'currency' => $tx->currency,
                        'type' => $tx->type,
                        'status' => $tx->status,
                        'date' => $tx->created_at->toDateTimeString(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * GET /api/rate.
     */
    public function getLiveRates(ExchangeService $exchangeService)
    {
        return response()->json([
            'rates' => [
                'CNY_TO_NGN' => $exchangeService->getRate('CNY', 'NGN'),
                'NGN_TO_CNY' => $exchangeService->getRate('NGN', 'CNY'),
            ],
            'last_updated' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Step 1: Initiate Swap & Generate Idempotency Key
     * POST /api/swap.
     */
    public function initiateSwap(Request $request, ExchangeService $exchangeService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from' => 'required|in:CNY,NGN',
            'to' => 'required|in:CNY,NGN|different:from',
        ]);

        $user = $request->user();
        $amountInSubunits = (int) round($request->amount * 100);

        // 1. Pre-flight Balance Check
        $sourceWallet = $user->wallets()->where('currency', $request->from)->first();
        if (!$sourceWallet || $sourceWallet->balance < $amountInSubunits) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance in your '.$request->from.' wallet.',
            ], 400);
        }

        // 2. Generate Transaction Identifiers
        // The idempotency_key ensures this specific intent can't be duplicated.
        $idempotencyKey = (string) Str::uuid();
        $otp = rand(100000, 999999);

        // 3. Get Current Market Rate
        $currentRate = $exchangeService->getRate($request->from, $request->to);
        $estimatedOutput = $exchangeService->convert($amountInSubunits, $request->from, $request->to);

        // 4. Secure the session
        // We store the hashed OTP and the metadata about the intended swap.
        $user->update([
            'two_factor_secret' => Hash::make($otp),
            // We can store the rate-lock or metadata in a cache or a 'pending_transactions' table
            // For this flow, we'll return it to the frontend to pass back during confirm.
        ]);

        /*
         * TRACEABILITY & AUDIT
         * Log the intent so we can track "Abandoned Swaps"
         */
        Log::info('Swap Initiated', [
            'user_id' => $user->id,
            'idempotency_key' => $idempotencyKey,
            'from' => $request->from,
            'amount' => $request->amount,
        ]);

        // 5. Mock Send OTP
        Log::info("TuPay OTP: {$otp}");

        return response()->json([
            'status' => 'success',
            'message' => 'A verification code has been sent to your email.',
            'data' => [
                'idempotency_key' => $idempotencyKey,
                'from_currency' => $request->from,
                'from_amount' => (float) $request->amount,
                'to_currency' => $request->to,
                'to_amount' => $estimatedOutput / 100,
                'rate' => $currentRate,
                'expires_in' => '5 minutes', // Advisory rate-lock
            ],
        ]);
    }

    /**
     * Step 2: Verify OTP
     * POST /api/swap/confirm.
     * Integrated with Redis Locking, DB Transactions, and Audit Metadata.
     */
    public function confirmSwap(Request $request, ExchangeService $exchangeService)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|digits:6',
            'amount' => 'required|numeric',
            'from' => 'required|in:CNY,NGN',
            'to' => 'required|in:CNY,NGN',
            'idempotency_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // 1. SAFETY: Redis Atomic Lock (Pessimistic Locking)
        $lockKey = "swap_lock_user_{$user->id}";
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return response()->json(['message' => 'Transaction in progress. Please wait.'], 429);
        }

        try {
            // 2. IDEMPOTENCY: Ensure we don't process the same UUID twice
            $existingTx = Transaction::where('metadata->idempotency_key', $request->idempotency_key)->first();
            if ($existingTx) {
                $lock->release();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Swap already processed',
                    'reference' => $existingTx->reference,
                ]);
            }

            // 3. SECURITY: Verify hashed OTP
            if (!$user->two_factor_secret || !Hash::check($request->code, $user->two_factor_secret)) {
                $lock->release();

                return response()->json(['message' => 'Invalid or expired OTP.'], 422);
            }

            // 4. PREPARATION: Calculation using Cached Rates
            $amountInSubunits = (int) round($request->amount * 100);
            $rate = $exchangeService->getRate($request->from, $request->to);
            $convertedAmount = $exchangeService->convert($amountInSubunits, $request->from, $request->to);
            $reference = 'SWP-'.strtoupper(Str::random(10));

            // Capture Full Audit Metadata
            $auditData = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => $this->getBrowserFromUserAgent($request->userAgent()),
                'device_id' => $request->header('X-Device-ID') ?? 'web-client',
                'idempotency_key' => $request->idempotency_key,
            ];

            // 5. ATOMICITY: All-or-nothing DB Transaction
            return DB::transaction(function () use ($user, $request, $amountInSubunits, $convertedAmount, $reference, $rate, $lock, $auditData) {
                // Ordered locking to prevent Deadlocks
                $wallets = Wallet::where('user_id', $user->id)
                    ->whereIn('currency', [$request->from, $request->to])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $sourceWallet = $wallets->where('currency', $request->from)->first();
                $destWallet = $wallets->where('currency', $request->to)->first();

                if (!$sourceWallet || $sourceWallet->balance < $amountInSubunits) {
                    throw new \Exception('Insufficient balance.');
                }

                // Balance Adjustments
                $oldSourceBalance = $sourceWallet->balance;
                $sourceWallet->decrement('balance', $amountInSubunits);

                $oldDestBalance = $destWallet->balance;
                $destWallet->increment('balance', $convertedAmount);

                // Record Double-Entry Ledger
                $this->recordSwap($sourceWallet, $destWallet, $amountInSubunits, $convertedAmount, $reference, $rate, $auditData);

                // Invalidate OTP
                $user->update(['two_factor_secret' => null]);

                $lock->release();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Swap completed successfully',
                    'reference' => $reference,
                    'data' => [
                        'from' => ['currency' => $request->from, 'amount' => $request->amount],
                        'to' => ['currency' => $request->to, 'amount' => $convertedAmount / 100],
                        'rate' => $rate,
                    ],
                ]);
            }, 3);
        } catch (\Exception $e) {
            $lock->release();

            return response()->json(['message' => 'Transaction failed: '.$e->getMessage()], 500);
        }
    }

    /**
     * Manual Funding (For Testing/Development)
     * POST /api/wallet/fund.
     */
    public function fundWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|in:CNY,NGN',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $amountInSubunits = (int) round($request->amount * 100);
        $reference = 'FND-'.strtoupper(Str::random(10));

        try {
            return DB::transaction(function () use ($user, $request, $amountInSubunits, $reference) {
                // 1. Lock the specific wallet
                $wallet = Wallet::where('user_id', $user->id)
                    ->where('currency', $request->currency)
                    ->lockForUpdate()
                    ->first();

                if (!$wallet) {
                    throw new \Exception("Wallet for {$request->currency} not found.");
                }

                $oldBalance = $wallet->balance;

                // 2. Increment Balance
                $wallet->increment('balance', $amountInSubunits);

                // 3. Create Transaction Audit Trail
                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'currency' => $wallet->currency,
                    'amount' => $amountInSubunits,
                    'balance_before' => $oldBalance,
                    'balance_after' => $wallet->balance,
                    'type' => 'deposit',
                    'reference' => $reference,
                    'status' => 'completed',
                    'metadata' => [
                        'ip_address' => $request->ip(),
                        'method' => 'manual_test_funding',
                        'browser' => $this->getBrowserFromUserAgent($request->userAgent()),
                    ],
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => "Successfully funded {$request->currency} wallet",
                    'data' => [
                        'new_balance' => $wallet->balance / 100,
                        'reference' => $reference,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Funding failed: '.$e->getMessage()], 500);
        }
    }

    /**
     * Settlement Partner Webhook
     * POST /api/webhooks/rmb-settlement.
     */
    public function handleRmbConfirmation(Request $request)
    {
        $data = $request->validate([
            'provider_reference' => 'required|string', // Partner's unique ID
            'internal_reference' => 'required|string', // Our SWP-... reference
            'status' => 'required|string|in:SUCCESS,FAILED',
        ]);

        // 1. Ensure the payload matches exactly what the partner sends
        $payload = json_encode([
            'provider_reference' => $request->provider_reference,
            'internal_reference' => $request->internal_reference,
        ]);

        // 2. Shared secret (stored in env)
        $secret = config('services.tupay.settlement.secret');

        // 3. Generate the HMAC SHA256 hash which will server as the webhook signature

        $tupaySignature = hash_hmac('sha256', $payload, $secret);

        $wehbookSignature = $request->bearerToken(); // I used Bearer Token coming with the webhook as the header signature here
        if ($wehbookSignature !== $tupaySignature) {
            Log::warning('Unauthorized Webhook Signature', ['ip' => $request->ip(), 'webhook_signature' => $wehbookSignature]);

            return response()->json(['status' => false, 'message' => 'Unauthorized Signature'], 401);
        }
        // 1. Idempotency Check
        // We check if this provider_reference has already been processed
        $alreadyProcessed = Transaction::where('metadata->provider_reference', $data['provider_reference'])->exists();

        if ($alreadyProcessed) {
            return response()->json(['status' => 'success', 'message' => 'Already processed'], 200);
        }

        // 2. Dispatch Asynchronous Job
        ProcessRmbSettlement::dispatch($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received and queued',
        ], 200);
    }

    /**
     * Get Wallet Ledger History (Paginated)
     * GET /api/ledger/{wallet_id}.
     */
    public function getLedgerHistory(Request $request, $wallet_id)
    {
        $user = $request->user();

        // 1. Verify ownership and existence of the wallet
        $wallet = Wallet::where('id', $wallet_id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or access denied.'], 404);
        }

        // 2. Fetch Paginated Transactions
        // We use latest() to show the most recent transactions first
        $ledger = Transaction::where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(15);

        // 3. Transform the data for the Frontend
        $ledger->getCollection()->transform(function ($tx) {
            return [
                'id' => $tx->id,
                'reference' => $tx->reference,
                'type' => strtoupper($tx->type),
                'amount' => (float) ($tx->amount / 100),
                'balance_before' => (float) ($tx->balance_before / 100),
                'balance_after' => (float) ($tx->balance_after / 100),
                'status' => $tx->status,
                'currency' => $tx->currency,
                'description' => $this->generateDescription($tx),
                'metadata' => $tx->metadata,
                'created_at' => $tx->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'wallet' => [
                'currency' => $wallet->currency,
                'current_balance' => $wallet->balance / 100,
            ],
            'history' => $ledger,
        ]);
    }

    /**
     * Helper to generate human-readable descriptions for the UI.
     */
    private function generateDescription($tx)
    {
        switch ($tx->type) {
            case 'swap_out':
                return 'Currency swap to '.($tx->metadata['target'] ?? 'Unknown');
            case 'swap_in':
                return 'Currency swap from '.($tx->metadata['source'] ?? 'Unknown');
            case 'deposit':
                return 'Wallet funding via '.($tx->metadata['method'] ?? 'external provider');
            default:
                return 'Transaction processed';
        }
    }

    /**
     * Double-Entry Recording Logic.
     */
    private function recordSwap($source, $dest, $debit, $credit, $ref, $rate, $audit)
    {
        // Debit Row
        Transaction::create([
            'wallet_id' => $source->id,
            'currency' => $source->currency,
            'amount' => -$debit,
            'balance_before' => $source->balance + $debit,
            'balance_after' => $source->balance,
            'type' => 'swap_out',
            'reference' => $ref,
            'status' => 'completed',
            'metadata' => array_merge($audit, ['target' => $dest->currency, 'rate' => $rate]),
        ]);

        // Credit Row
        Transaction::create([
            'wallet_id' => $dest->id,
            'currency' => $dest->currency,
            'amount' => $credit,
            'balance_before' => $dest->balance - $credit,
            'balance_after' => $dest->balance,
            'type' => 'swap_in',
            'reference' => $ref.'-IN',
            'status' => 'completed',
            'metadata' => array_merge($audit, ['source' => $source->currency]),
        ]);
    }

    /**
     * Browser Detection Helper.
     */
    private function getBrowserFromUserAgent($userAgent)
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR')) {
            return 'Opera';
        }
        if (strpos($userAgent, 'Edge')) {
            return 'Edge';
        }
        if (strpos($userAgent, 'Chrome')) {
            return 'Chrome';
        }
        if (strpos($userAgent, 'Safari')) {
            return 'Safari';
        }
        if (strpos($userAgent, 'Firefox')) {
            return 'Firefox';
        }
        if (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/7')) {
            return 'Internet Explorer';
        }

        return 'Other/Mobile';
    }
}
