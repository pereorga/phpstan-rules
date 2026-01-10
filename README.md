# phpstan-rules

Opinionated rules for [PHPStan](https://github.com/phpstan/phpstan).

## Installation

Run:

```sh
composer require --dev pereorga/phpstan-rules
```

## Usage

All provided [rules](https://github.com/pereorga/phpstan-rules#rules) are included in [`rules.neon`](rules.neon).

To enable them, reference `rules.neon` in your `phpstan.neon` file:

```neon
includes:
    - vendor/pereorga/phpstan-rules/rules.neon
```

## Rules

### `NoNonReferenceParameterAssignmentRule`

Disallows assigning values to parameters that are not passed by reference. Encourages the use of separate working variables.

### `NoSuperfluousPhpDocTypesRule`

Detects `@param` and `@return` tags that redundantly duplicate type information already present in native type declarations. Encourages using prose descriptions (e.g., "The name parameter specifies...") for simple types, and `@param`/`@return` tags only for complex types (array shapes, generics).

### `SnakeCaseFunctionRule`

Requires all function names to be written in `snake_case`.

### `SnakeCaseParameterRule`

Requires all parameter names to be written in `snake_case`.

### `SnakeCaseVariableRule`

Requires all variable names to be written in `snake_case`.
