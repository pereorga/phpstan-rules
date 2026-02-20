<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\SnakeCaseFunctionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SnakeCaseFunctionRule>
 *
 * @internal
 *
 * @coversNothing
 */
final class SnakeCaseFunctionRuleTest extends RuleTestCase
{
    public function testValidSnakeCaseFunctions(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-function-valid.php'], []);
    }

    public function testInvalidFunctionNames(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-function-invalid.php'], [
            [
                "'camelCase' function name is not in snake_case format.",
                3,
            ],
            [
                "'PascalCase' function name is not in snake_case format.",
                8,
            ],
            [
                "'mixedCase_function' function name is not in snake_case format.",
                13,
            ],
            [
                "'UPPERCASE' function name is not in snake_case format.",
                18,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new SnakeCaseFunctionRule();
    }
}
