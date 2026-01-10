<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\SnakeCaseParameterRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SnakeCaseParameterRule>
 */
final class SnakeCaseParameterRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SnakeCaseParameterRule();
    }

    public function testValidSnakeCaseParameters(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-parameter-valid.php'], []);
    }

    public function testInvalidParameterNames(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-parameter-invalid.php'], [
            [
                "'camelCase' parameter is not in snake_case format.",
                3,
            ],
            [
                "'PascalCase' parameter is not in snake_case format.",
                8,
            ],
            [
                "'mixedCase_param' parameter is not in snake_case format.",
                13,
            ],
        ]);
    }
}
