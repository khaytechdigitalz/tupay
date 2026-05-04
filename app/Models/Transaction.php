<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'currency',
        'amount',
        'balance_before',
        'balance_after',
        'type',
        'reference',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     * This allows us to treat JSON metadata as a PHP array automatically.
     */
    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * Relationship to the Wallet.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Scope to filter by type (useful for the Paginated History requirement).
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
