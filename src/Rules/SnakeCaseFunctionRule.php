<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Function_>
 */
final class SnakeCaseFunctionRule implements Rule
{
    public function getNodeType(): string
    {
        return Function_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $function_name = $node->name->toString();
        if (preg_match('/^[a-z\d]+(?:_[a-z\d]+)*$/', $function_name) !== 1) {
            return [
                RuleErrorBuilder::message(
                    "'{$function_name}' function name is not in snake_case format."
                )
                    ->identifier('noSnakeCaseFunctionNameFormat')
                    ->build(),
            ];
        }

        return [];
    }
}
