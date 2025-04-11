# phpstan-rules

This project provides a [Composer](https://getcomposer.org) package with some opinionated rules for [PHPStan](https://github.com/phpstan/phpstan).

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

This package provides the following rules:

- [`PereOrga\PHPStanRules\Rules\NonRefParameterModificationRule`](https://github.com/pereorga/phpstan-rules#nonrefparametermodificationrule)
- [`PereOrga\PHPStanRules\Rules\SnakeCaseFunctionRule`](https://github.com/pereorga/phpstan-rules#snakecasefunctionrule)
- [`PereOrga\PHPStanRules\Rules\SnakeCaseParameterRule`](https://github.com/pereorga/phpstan-rules#snakecaseparameterrule)
- [`PereOrga\PHPStanRules\Rules\SnakeCaseVariableRule`](https://github.com/pereorga/phpstan-rules#snakecasevariablerule)

### `NonRefParameterModificationRule`

Disallows assigning values to parameters that are not passed by reference. Encourages the use of separate working variables.

### `SnakeCaseFunctionRule`

Requires all function names to be written in `snake_case`.

### `SnakeCaseParameterRule`

Requires all parameter names to be written in `snake_case`.

### `SnakeCaseVariableRule`

Requires all variable names to be written in `snake_case`.
