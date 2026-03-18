# phpstan-rules

Opinionated rules for [PHPStan](https://github.com/phpstan/phpstan).

## Installation

Run:

```sh
composer require --dev pereorga/phpstan-rules
```

## Usage

To enable all [rules](https://github.com/pereorga/phpstan-rules#rules), reference [`rules.neon`](rules.neon) in your `phpstan.neon` file:

```neon
includes:
    - vendor/pereorga/phpstan-rules/rules.neon
```

To enable only specific rules, include their individual configuration files from the [`rules/`](rules/) directory:

```neon
includes:
    - vendor/pereorga/phpstan-rules/rules/no-redundant-boolean-comparison.neon
    - vendor/pereorga/phpstan-rules/rules/snake-case-variable.neon
```

## Rules

### `NoRedundantBooleanComparisonRule`

[`rules/no-redundant-boolean-comparison.neon`](rules/no-redundant-boolean-comparison.neon)

Disallows comparing boolean expressions with `=== true`, `=== false`, `!== true`, or `!== false`. Use the boolean value directly instead.

### `NoNonReferenceParameterAssignmentRule`

[`rules/no-non-reference-parameter-assignment.neon`](rules/no-non-reference-parameter-assignment.neon)

Disallows assigning values to parameters that are not passed by reference. Encourages the use of separate working variables.

### `NoSuperfluousPhpDocTypesRule`

[`rules/no-superfluous-phpdoc-types.neon`](rules/no-superfluous-phpdoc-types.neon)

Detects `@param` and `@return` tags that redundantly duplicate type information already present in native type declarations. Encourages using prose descriptions (e.g., "The name parameter specifies...") for simple types, and `@param`/`@return` tags only for complex types (array shapes, generics).

### `SnakeCaseFunctionRule`

[`rules/snake-case-function.neon`](rules/snake-case-function.neon)

Requires all function names to be written in `snake_case`.

### `SnakeCaseParameterRule`

[`rules/snake-case-parameter.neon`](rules/snake-case-parameter.neon)

Requires all parameter names to be written in `snake_case`.

### `SnakeCaseVariableRule`

[`rules/snake-case-variable.neon`](rules/snake-case-variable.neon)

Requires all variable names to be written in `snake_case`.
