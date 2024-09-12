<?php

use Illuminate\Support\Str;

if (! function_exists('to_snake')) {
    function to_snake($array)
    {
        return array_reduce(array_flip($array), function ($carry, $item) use ($array) {
            $carry[Str::snake($item)] = $array[$item];
            return $carry;
        }, []);
    }
}
