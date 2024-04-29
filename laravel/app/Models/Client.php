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

    protected $fillable = ['first_name', 'last_name', 'middle_name', 'email', 'user_id', 'preferred_name', 'home_phone', 'work_phone', 'mobile_phone', 'fax', 'physical_address_id', 'postal_address_id'];

    protected $hidden = ['deleted_at'];

    public function physicalAddress()
    {
        return $this->belongsTo(Address::class, 'physical_address_id');
    }

    public function postalAddress()
    {
        return $this->belongsTo(Address::class, 'postal_address_id');
    }

    public function customFields()
    {
        return $this->morphOne(CustomField::class, 'custom_fieldable')->withDefault([
            "data" => json_encode([])
        ]);
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
            'physical_address.town' => (string) $this->physicalAddress->town,
            'physical_address.street_name' => (string) $this->physicalAddress->street_name,
            'custom_fields.data' => (string) $this->customFields?->data,
        ]);
    }

    protected function makeAllSearchableUsing(Builder $query)
    {
        return $query->with(['physicalAddress', 'postalAddress', 'customFields']);
    }
}
