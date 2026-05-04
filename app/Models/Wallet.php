<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'currency', 'balance'];

    // Relationship: Wallet belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: A wallet has many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Helper to get decimal balance
     * Example: 10050 becomes 100.50.
     */
    public function getDecimalBalanceAttribute()
    {
        return number_format($this->balance / 100, 2, '.', '');
    }
}
