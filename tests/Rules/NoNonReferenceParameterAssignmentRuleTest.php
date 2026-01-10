<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\NoNonReferenceParameterAssignmentRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoNonReferenceParameterAssignmentRule>
 */
final class NoNonReferenceParameterAssignmentRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoNonReferenceParameterAssignmentRule();
    }

    public function testValidUsage(): void
    {
        $this->analyse([__DIR__ . '/data/no-parameter-assignment-valid.php'], []);
    }

    public function testInvalidParameterAssignment(): void
    {
        $this->analyse([__DIR__ . '/data/no-parameter-assignment-invalid.php'], [
            [
                "Non-reference parameter 'value' is being modified. Create a working variable instead.",
                6,
            ],
            [
                "Non-reference parameter 'data' is being modified. Create a working variable instead.",
                11,
            ],
            [
                "Non-reference parameter 'count' is being modified. Create a working variable instead.",
                19,
            ],
        ]);
    }
}
