<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Money implements CastsAttributes
{

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return  \Brick\Money\Money::ofMinor($value, $attributes['currency']);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value instanceof \Brick\Money\Money) {
            return (int)($value * 100);
        }
        return $value->getMinorAmount()->toInt();
    }
}
