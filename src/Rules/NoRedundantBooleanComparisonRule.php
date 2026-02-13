<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Disallows comparing boolean expressions with `=== true`, `=== false`,
 * `!== true`, or `!== false`. Use the boolean directly instead.
 *
 * @implements Rule<Node\Expr\BinaryOp>
 */
final class NoRedundantBooleanComparisonRule implements Rule
{
    private Standard $printer;

    public function __construct()
    {
        $this->printer = new Standard();
    }

    public function getNodeType(): string
    {
        return Node\Expr\BinaryOp::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Identical && !$node instanceof NotIdentical) {
            return [];
        }

        $boolean_literal = null;
        $other_side = null;

        if ($this->isBooleanLiteral($node->right)) {
            $boolean_literal = $node->right;
            $other_side = $node->left;
        } elseif ($this->isBooleanLiteral($node->left)) {
            $boolean_literal = $node->left;
            $other_side = $node->right;
        }

        if ($boolean_literal === null || $other_side === null) {
            return [];
        }

        $other_type = $scope->getType($other_side);
        if (!$other_type->isBoolean()->yes()) {
            return [];
        }

        $literal_name = $this->getBooleanLiteralName($boolean_literal);
        $is_negated = ($node instanceof Identical && $literal_name === 'false')
            || ($node instanceof NotIdentical && $literal_name === 'true');

        $printed_other = $this->printer->prettyPrintExpr($other_side);
        $suggestion = $is_negated ? "!{$printed_other}" : $printed_other;

        $operator = $node instanceof Identical ? '===' : '!==';
        if ($this->isBooleanLiteral($node->left)) {
            $printed_original = "{$literal_name} {$operator} {$printed_other}";
        } else {
            $printed_original = "{$printed_other} {$operator} {$literal_name}";
        }

        $suggestion_text = $is_negated
            ? "use `{$suggestion}` instead of `{$printed_original}`"
            : "use `{$suggestion}` directly instead of `{$printed_original}`";

        return [
            RuleErrorBuilder::message("Redundant comparison: {$suggestion_text}.")
                ->identifier('noRedundantBooleanComparison')
                ->build(),
        ];
    }

    private function isBooleanLiteral(Node\Expr $expr): bool
    {
        if (!$expr instanceof ConstFetch) {
            return false;
        }

        $name = strtolower($expr->name->name);

        return $name === 'true' || $name === 'false';
    }

    private function getBooleanLiteralName(Node\Expr $expr): string
    {
        \assert($expr instanceof ConstFetch);

        return strtolower($expr->name->name);
    }
}
