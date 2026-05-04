<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRmbSettlement implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $providerRef = $this->payload['provider_reference'];
        $internalRef = $this->payload['internal_reference'];

        // 1. Find the transaction
        $transaction = Transaction::where('reference', $internalRef)->first();

        // 2. Only update if it exists and isn't already completed
        if ($transaction && $transaction->status !== 'completed') {
            $transaction->update([
                'status' => 'completed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'provider_reference' => $providerRef,
                    'settled_at' => now()->toDateTimeString(),
                    'processed_by' => 'RmbSettlementWorker',
                ]),
            ]);

            Log::info("Transaction {$internalRef} marked as completed via webhook.");
        }
    }
}
