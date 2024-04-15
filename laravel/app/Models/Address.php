<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['building', 'floor_unit_no', 'street_name', 'street_type', 'town', 'state_county', 'box_type_no', 'country'];
}
