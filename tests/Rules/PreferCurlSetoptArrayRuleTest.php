<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Tests\Rules;

use PereOrga\PHPStanRules\Rules\PreferCurlSetoptArrayRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PreferCurlSetoptArrayRule>
 *
 * @internal
 *
 * @coversNothing
 */
final class PreferCurlSetoptArrayRuleTest extends RuleTestCase
{
    private const MESSAGE = 'Consecutive curl option setters should use a single curl_setopt_array() call.';

    public function testValidData(): void
    {
        $this->analyse([__DIR__ . '/data/prefer-curl-setopt-array-valid.php'], []);
    }

    public function testInvalidData(): void
    {
        $this->analyse([__DIR__ . '/data/prefer-curl-setopt-array-invalid.php'], [
            [
                self::MESSAGE,
                7,
            ],
            [
                self::MESSAGE,
                14,
            ],
            [
                self::MESSAGE,
                15,
            ],
            [
                self::MESSAGE,
                22,
            ],
            [
                self::MESSAGE,
                29,
            ],
            [
                self::MESSAGE,
                37,
            ],
            [
                self::MESSAGE,
                45,
            ],
            [
                self::MESSAGE,
                51,
            ],
            [
                self::MESSAGE,
                63,
            ],
            [
                self::MESSAGE,
                69,
            ],
            [
                self::MESSAGE,
                73,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new PreferCurlSetoptArrayRule();
    }
}
