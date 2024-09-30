<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'container',
        'base_type',
        'transaction_id',
        'amount',
        'currency',
        'category_type',
        'category_id',
        'category',
        'category_source',
        'high_level_category_id',
        'created_date',
        'last_updated',
        'description',
        'is_manual',
        'source_type',
        'transaction_date',
        'post_date',
        'status',
        'account_id',
        'running_balance',
        'check_number',
        'batch_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
