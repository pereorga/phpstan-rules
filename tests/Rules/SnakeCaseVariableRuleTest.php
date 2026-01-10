<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\SnakeCaseVariableRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SnakeCaseVariableRule>
 */
final class SnakeCaseVariableRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SnakeCaseVariableRule();
    }

    public function testValidSnakeCaseVariables(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-variable-valid.php'], []);
    }

    public function testInvalidVariableNames(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-variable-invalid.php'], [
            [
                "'camelCase' variable name is not in snake_case format.",
                3,
            ],
            [
                "'PascalCase' variable name is not in snake_case format.",
                4,
            ],
            [
                "'mixedCase_var' variable name is not in snake_case format.",
                5,
            ],
        ]);
    }
}
