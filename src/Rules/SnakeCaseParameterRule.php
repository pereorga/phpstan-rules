<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Param>
 */
final class SnakeCaseParameterRule implements Rule
{
    public function getNodeType(): string
    {
        return Param::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->var instanceof Node\Expr\Variable) {
            return [];
        }

        if (!is_string($node->var->name)) {
            return [];
        }

        $param_name = $node->var->name;
        if (preg_match('/^[a-z\d]+(?:_[a-z\d]+)*$/', $param_name) !== 1) {
            return [
                RuleErrorBuilder::message(
                    "'{$param_name}' parameter is not in snake_case format."
                )
                    ->identifier('noSnakeCaseParameterFormat')
                    ->build(),
            ];
        }

        return [];
    }
}
