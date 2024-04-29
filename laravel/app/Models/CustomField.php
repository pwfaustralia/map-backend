<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['custom_fieldable_id', 'custom_fieldable_type', 'value'];

    public function custom_fieldable()
    {
        return $this->morphTo();
    }
}
