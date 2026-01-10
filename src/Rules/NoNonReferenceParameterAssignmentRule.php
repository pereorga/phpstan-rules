<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Assign>
 */
final class NoNonReferenceParameterAssignmentRule implements Rule
{
    public function getNodeType(): string
    {
        return Assign::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Only check direct variable assignments.
        if (!$node->var instanceof Node\Expr\Variable) {
            return [];
        }

        if (!\is_string($node->var->name)) {
            return [];
        }

        $var_name = $node->var->name;
        $function = $scope->getFunction();
        if ($function === null) {
            return [];
        }

        $function_parameters = $function->getParameters();

        foreach ($function_parameters as $parameter) {
            if ($parameter->getName() === $var_name) {
                // Ignore parameter if it is passed by reference.
                if ($parameter->passedByReference()->yes()) {
                    continue;
                }

                return [
                    RuleErrorBuilder::message(
                        "Non-reference parameter '{$var_name}' is being modified. Create a working variable instead."
                    )
                        ->identifier('modifyingNonRefParameter')
                        ->build(),
                ];
            }
        }

        return [];
    }
}
