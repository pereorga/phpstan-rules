<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\PDOFetchModeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PDOFetchModeRule>
 *
 * @internal
 *
 * @coversNothing
 */
final class PDOFetchModeRuleTest extends RuleTestCase
{
    public function testValidData(): void
    {
        $this->analyse([__DIR__ . '/data/pdo-fetch-mode-valid.php'], []);
    }

    public function testInvalidData(): void
    {
        $this->analyse([__DIR__ . '/data/pdo-fetch-mode-invalid.php'], [
            [
                'Method PDOStatement::fetch() should be called with a fetch mode.',
                7,
            ],
            [
                'Method PDOStatement::fetchAll() should be called with a fetch mode.',
                8,
            ],
            [
                'Method PDOStatement::fetch() should be called with a fetch mode.',
                11,
            ],
            [
                'Method PDOStatement::fetchAll() should be called with a fetch mode.',
                12,
            ],
            [
                'Method PDOStatement::fetchAll() should be called with a fetch mode.',
                15,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new PDOFetchModeRule();
    }
}
