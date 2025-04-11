<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Variable>
 */
final class SnakeCaseVariableRule implements Rule
{
    /**
     * List of PHP superglobals to ignore.
     */
    private const SUPERGLOBALS = [
        '_GET', '_POST', '_REQUEST', '_COOKIE', '_SESSION', '_SERVER',
        '_ENV', '_FILES', 'GLOBALS',
    ];

    public function getNodeType(): string
    {
        return Variable::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Skip dynamic variable names like ${$expr}.
        if (!is_string($node->name)) {
            return [];
        }

        $variable_name = $node->name;
        if (in_array($variable_name, self::SUPERGLOBALS, true)) {
            return [];
        }

        if (preg_match('/^[a-z\d]+(?:_[a-z\d]+)*$/', $variable_name) !== 1) {
            return [
                RuleErrorBuilder::message(
                    "'{$variable_name}' variable name is not in snake_case format."
                )
                    ->identifier('noSnakeCaseVariableNameFormat')
                    ->build(),
            ];
        }

        return [];
    }
}
