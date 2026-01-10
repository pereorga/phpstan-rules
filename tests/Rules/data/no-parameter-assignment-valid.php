<?php

// Reference parameters are allowed to be modified
function modify_by_reference(string &$value): void
{
    $value = 'modified';
}

// Using a working variable is the correct approach
function process_with_working_var(string $value): string
{
    $working_value = $value;
    $working_value = strtoupper($working_value);
    return $working_value;
}

// Not modifying the parameter at all
function read_only(string $value): string
{
    return strtoupper($value);
}

// Modifying properties of objects (not the parameter itself)
function modify_object_property(object $obj): void
{
    $obj->property = 'value';
}

// Array access doesn't count as reassignment
function modify_array_element(array $arr): void
{
    $arr['key'] = 'value';
}

class ValidClass
{
    public function method_with_reference(int &$ref_param): void
    {
        $ref_param = 42;
    }

    public function method_with_working_var(int $param): int
    {
        $working = $param;
        $working = $working * 2;
        return $working;
    }
}
