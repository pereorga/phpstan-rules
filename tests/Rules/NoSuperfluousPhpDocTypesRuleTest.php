<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\NoSuperfluousPhpDocTypesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoSuperfluousPhpDocTypesRule>
 *
 * @internal
 *
 * @coversNothing
 */
final class NoSuperfluousPhpDocTypesRuleTest extends RuleTestCase
{
    public function testValidPhpDocUsage(): void
    {
        $this->analyse([__DIR__ . '/data/no-superfluous-phpdoc-valid.php'], []);
    }

    public function testSuperfluousPhpDocTypes(): void
    {
        $this->analyse([__DIR__ . '/data/no-superfluous-phpdoc-invalid.php'], [
            [
                '@param tag for parameter $name has type string which is already declared in the signature.',
                10,
            ],
            [
                '@return tag has type int which is already declared in the signature.',
                10,
            ],
            [
                '@param tag for parameter $value has type int which is already declared in the signature.',
                19,
            ],
            [
                '@return tag has type void which is already declared in the signature.',
                19,
            ],
            [
                '@param tag for parameter $flag has type bool which is already declared in the signature.',
                27,
            ],
            [
                '@return tag has type bool which is already declared in the signature.',
                27,
            ],
            [
                '@param tag for parameter $price has type float which is already declared in the signature.',
                36,
            ],
            [
                '@return tag has type float which is already declared in the signature.',
                36,
            ],
            [
                '@param tag for parameter $obj has type \stdClass which is already declared in the signature.',
                44,
            ],
            [
                '@param tag for parameter $name has type ?string which is already declared in the signature.',
                65,
            ],
            [
                '@return tag has type ?int which is already declared in the signature.',
                65,
            ],
            [
                '@param tag for parameter $name has type string|null which is already declared in the signature.',
                74,
            ],
            [
                '@return tag has type int|null which is already declared in the signature.',
                74,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoSuperfluousPhpDocTypesRule();
    }
}
