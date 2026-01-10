<?php

// No PHPDoc at all - valid
function no_phpdoc(string $name): int
{
    return strlen($name);
}

// PHPDoc with only description (no type) - valid
/**
 * Processes user data with validation.
 */
function with_description(string $name): void
{
}

// Array types need PHPDoc for generics - valid
/**
 * @param array<string, int> $data
 * @return array<int, string>
 */
function array_generics(array $data): array
{
    return [];
}

// Array shapes need PHPDoc - valid
/**
 * @param array{name: string, age: int} $user
 */
function array_shapes(array $user): void
{
}

// No native type, PHPDoc is needed - valid
/**
 * @param string $name
 * @return int
 */
function no_native_types($name)
{
    return 0;
}

// Union types need PHPDoc - valid
/**
 * @param string|int $value
 */
function union_types($value): void
{
}

// Nullable types are redundant with native types, so no PHPDoc needed - valid
function nullable_param(?string $name): void
{
}

function nullable_return(): ?int
{
    return null;
}

function question_mark_nullable(?string $value): void
{
}

// Callable types with details - valid
/**
 * @param callable(string, int): bool $callback
 */
function callable_with_details(callable $callback): void
{
}

// Iterable with generics - valid
/**
 * @param iterable<int, string> $items
 */
function iterable_generics(iterable $items): void
{
}

// Mixed type doesn't need PHPDoc - valid
function mixed_type(mixed $value): mixed
{
    return $value;
}

// Class with only necessary PHPDoc
class ValidClass
{
    /**
     * @param array<string> $items
     */
    public function process_items(array $items): void
    {
    }

    public function no_phpdoc_needed(string $value): string
    {
        return $value;
    }
}
