<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = ['created_at', 'updated_at'];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
