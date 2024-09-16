<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Client extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Searchable;

    protected $fillable = ['first_name', 'last_name', 'middle_name', 'email', 'user_id', 'preferred_name', 'home_phone', 'work_phone', 'mobile_phone', 'fax', 'address_1', 'address_2', 'city', 'postcode', 'state', 'country', 'yodlee_username', 'yodlee_status'];

    protected $hidden = ['deleted_at'];

    public function customFields()
    {
        return $this->morphOne(CustomField::class, 'custom_fieldable')->withDefault([
            "data" => json_encode([])
        ]);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Account::class, 'client_id', 'account_id', 'id', 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toSearchableArray()
    {
        return array_merge($this->toArray(), [
            'id' => (string) $this->id,
            'created_at' => $this->created_at->timestamp,
            'custom_fields.data' => (string) $this->customFields?->data,
            'address_2' => (string) $this->address_2 ?? ""
        ]);
    }

    protected function makeAllSearchableUsing(Builder $query)
    {
        return $query->with(['customFields']);
    }
}
