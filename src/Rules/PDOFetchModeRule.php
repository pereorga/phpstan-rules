<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
final class PDOFetchModeRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $method_name = strtolower($node->name->toString());
        if (!\in_array($method_name, ['fetch', 'fetchall'], true)) {
            return [];
        }

        $type = $scope->getType($node->var);
        $pdo_statement_type = new ObjectType('PDOStatement');

        if ($pdo_statement_type->isSuperTypeOf($type)->no()) {
            return [];
        }

        if (\count($node->getArgs()) > 0) {
            return [];
        }

        $original_method_name = $node->name->toString();

        return [
            RuleErrorBuilder::message("Method PDOStatement::{$original_method_name}() should be called with a fetch mode.")
                ->identifier('pdoFetchMode')
                ->build(),
        ];
    }
}
