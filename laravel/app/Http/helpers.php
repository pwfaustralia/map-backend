<?php

if (! function_exists('camelToSnakeCaseArray')) {
    function camelToSnakeCaseArray(array $array): array
    {
        $convertedArray = [];

        foreach ($array as $key => $value) {
            // Convert camelCase to snake_case using a regex
            $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));

            // Assign the value to the new key
            $convertedArray[$snakeKey] = $value;
        }

        return $convertedArray;
    }
}
