# PHPStan Rules Test Suite

This directory contains comprehensive tests for all custom PHPStan rules in this package.

## Running Tests

### Using Composer Scripts (Recommended)

```bash
composer test              # Run tests with testdox output
composer test:coverage     # Run tests with code coverage report
```

### Using PHPUnit Directly

```bash
./phpunit.phar --testdox                    # Run all tests
./phpunit.phar --filter SnakeCaseFunction   # Run specific test
./phpunit.phar --coverage-html coverage/    # Generate HTML coverage report
```

## Test Structure

The test suite follows PHPStan's testing conventions using `RuleTestCase`:

```
tests/
├── Rules/
│   ├── SnakeCaseFunctionRuleTest.php
│   ├── SnakeCaseVariableRuleTest.php
│   ├── SnakeCaseParameterRuleTest.php
│   ├── NoNonReferenceParameterAssignmentRuleTest.php
│   ├── NoSuperfluousPhpDocTypesRuleTest.php
│   └── data/
│       ├── snake-case-function-valid.php
│       ├── snake-case-function-invalid.php
│       ├── snake-case-variable-valid.php
│       ├── snake-case-variable-invalid.php
│       ├── snake-case-parameter-valid.php
│       ├── snake-case-parameter-invalid.php
│       ├── no-parameter-assignment-valid.php
│       ├── no-parameter-assignment-invalid.php
│       ├── no-superfluous-phpdoc-valid.php
│       └── no-superfluous-phpdoc-invalid.php
└── README.md
```

## Test Coverage

### SnakeCaseFunctionRule
- ✅ Valid snake_case function names
- ✅ Invalid camelCase, PascalCase, and mixed formats
- ✅ Function names with numbers

### SnakeCaseVariableRule
- ✅ Valid snake_case variable names
- ✅ Invalid camelCase, PascalCase, and mixed formats
- ✅ PHP superglobals exception handling
- ✅ Variable names with numbers

### SnakeCaseParameterRule
- ✅ Valid snake_case parameter names
- ✅ Invalid camelCase, PascalCase, and mixed formats
- ✅ Function and method parameters
- ✅ Parameter names with numbers

### NoNonReferenceParameterAssignmentRule
- ✅ Valid reference parameter modifications
- ✅ Valid working variable approach
- ✅ Invalid direct parameter modifications
- ✅ Detection in functions and methods
- ✅ Array element and object property modifications (allowed)

### NoSuperfluousPhpDocTypesRule
- ✅ No PHPDoc when native types are sufficient
- ✅ PHPDoc required for array generics (array<T>)
- ✅ PHPDoc required for array shapes (array{key: type})
- ✅ PHPDoc required for union types (T|U)
- ✅ PHPDoc required for callable signatures
- ✅ PHPDoc required when no native type exists
- ✅ Detection of redundant simple types (string, int, bool, float)
- ✅ Detection of redundant class types (FQCN and short names)
- ✅ Nullable types are considered redundant with native ?Type

## Writing New Tests

To add tests for a new rule:

1. Create a test class extending `PHPStan\Testing\RuleTestCase`:

```php
<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\YourNewRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<YourNewRule>
 */
final class YourNewRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new YourNewRule();
    }

    public function testValidCases(): void
    {
        $this->analyse([__DIR__ . '/data/your-rule-valid.php'], []);
    }

    public function testInvalidCases(): void
    {
        $this->analyse([__DIR__ . '/data/your-rule-invalid.php'], [
            [
                'Error message here',
                5,  // Line number
            ],
        ]);
    }
}
```

2. Create fixture files in `tests/Rules/data/`:
   - `your-rule-valid.php` - Code that should NOT trigger the rule
   - `your-rule-invalid.php` - Code that SHOULD trigger the rule

3. Run the tests to verify they pass:

```bash
composer test
```

## Requirements

- PHP 8.1+
- PHPUnit 12.5+
- PHPStan 2.0+

## Continuous Integration

These tests can be integrated into your CI pipeline:

```bash
# In your CI configuration
composer install --no-interaction
composer test
```
