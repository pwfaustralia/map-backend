<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['deposit', 'balance', 'currency', 'loan_account_id', 'month', 'scenario', 'offset_amount', 'credit_card_amount', 'offset_balance'];
    protected $casts = [
        'deposit' => Money::class,
        'balance' => Money::class,
        'offset_amount' => Money::class,
        'credit_card_amount' => Money::class,
        'offset_balance' => Money::class
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
