<?php

// Redundant @param and @return for simple string type
/**
 * Processes a name.
 *
 * @param string $name
 * @return int
 */
function redundant_string_param(string $name): int
{
    return strlen($name);
}
// Redundant @param and @return for int type
/**
 * @param int $value
 * @return void
 */
function redundant_int_param(int $value): void
{
}
// Redundant @param and @return for bool type
/**
 * @param bool $flag
 * @return bool
 */
function redundant_bool_param(bool $flag): bool
{
    return !$flag;
}
// Redundant @param and @return for float type
/**
 * @param float $price
 * @return float
 */
function redundant_float_param(float $price): float
{
    return $price * 1.1;
}
// Redundant class type (using FQCN)
/**
 * @param \stdClass $obj
 */
function redundant_class_type(\stdClass $obj): void
{
}

class InvalidClass
{
    // Redundant in class methods too
    /**
     * @param string $name
     * @return string
     */
    public function redundant_method(string $name): string
    {
        return strtoupper($name);
    }
}

/**
 * @param ?string $name
 * @return ?int
 */
function redundant_nullable_short(?string $name): ?int
{
    return null;
}

/**
 * @param string|null $name
 * @return int|null
 */
function redundant_nullable_long(?string $name): ?int
{
    return null;
}