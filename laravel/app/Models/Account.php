<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['account_id', 'client_id', 'created_date', 'last_updated', 'batch_id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id', 'account_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
