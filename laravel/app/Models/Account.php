<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = ['account_id', 'client_id', 'created_date', 'last_updated', 'batch_id', 'is_primary', 'container', 'original_loan_amount', 'currency'];

    protected $casts = [
        'original_loan_amount' => Money::class
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id', 'account_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
