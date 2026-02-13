<?php

$success = true;

// Simple boolean conditions are fine
if ($success) {
}

if (!$success) {
}

// Comparing non-boolean values with === false is fine (e.g. strpos)
$pos = strpos('hello', 'h');
if ($pos === false) {
}

if ($pos !== false) {
}

// Nullable boolean comparisons are valid (not redundant)
/** @var bool|null $nullable */
$nullable = null;
if ($nullable === true) {
}

if ($nullable === false) {
}

if ($nullable !== true) {
}

if ($nullable !== false) {
}

// Mixed type comparisons are fine
/** @var mixed $mixed */
$mixed = null;
if ($mixed === false) {
}
