<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\NoRedundantBooleanComparisonRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoRedundantBooleanComparisonRule>
 */
final class NoRedundantBooleanComparisonRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRedundantBooleanComparisonRule();
    }

    public function testValidBooleanUsage(): void
    {
        $this->analyse([__DIR__ . '/data/no-redundant-boolean-comparison-valid.php'], []);
    }

    public function testInvalidBooleanComparisons(): void
    {
        $this->analyse([__DIR__ . '/data/no-redundant-boolean-comparison-invalid.php'], [
            [
                'Redundant comparison: use `$success` directly instead of `$success === true`.',
                5,
            ],
            [
                'Redundant comparison: use `!$success` instead of `$success === false`.',
                8,
            ],
            [
                'Redundant comparison: use `!$success` instead of `$success !== true`.',
                11,
            ],
            [
                'Redundant comparison: use `$success` directly instead of `$success !== false`.',
                14,
            ],
            [
                'Redundant comparison: use `$success` directly instead of `true === $success`.',
                17,
            ],
            [
                'Redundant comparison: use `!$success` instead of `false === $success`.',
                20,
            ],
        ]);
    }
}
