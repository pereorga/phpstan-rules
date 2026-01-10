<?php

// Modifying non-reference parameter
function invalid_function(string $value): string
{
    $value = strtoupper($value);
    return $value;
}
function another_invalid(array $data): array
{
    $data = array_filter($data);
    return $data;
}

class InvalidClass
{
    public function invalid_method(int $count): int
    {
        $count = $count * 2;
        return $count;
    }
}
