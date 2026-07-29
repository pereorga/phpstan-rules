<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Disallows multiple subsequent curl_setopt() calls on the same handle.
 * Prefer a single curl_setopt_array() call instead.
 *
 * @implements Rule<Node>
 */
final class PreferCurlSetoptArrayRule implements Rule
{
    private const FUNCTION_CURL_SETOPT = 'curl_setopt';
    private const FUNCTION_CURL_SETOPT_ARRAY = 'curl_setopt_array';

    private Standard $printer;

    public function __construct()
    {
        $this->printer = new Standard();
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $statements = $this->getStatements($node);
        if ($statements === null || \count($statements) < 2) {
            return [];
        }

        $errors = [];
        $calls = [];

        foreach ($statements as $statement) {
            $call = $this->getCurlOptionSetterCall($statement);
            if ($call === null) {
                $errors = array_merge($errors, $this->getErrorsForCallSequence($calls));
                $calls = [];

                continue;
            }

            if ($calls !== [] && $call['handle_key'] !== $calls[\count($calls) - 1]['handle_key']) {
                $errors = array_merge($errors, $this->getErrorsForCallSequence($calls));
                $calls = [];
            }

            $calls[] = $call;
        }

        return array_merge($errors, $this->getErrorsForCallSequence($calls));
    }

    /**
     * @return null|list<Node\Stmt>
     */
    private function getStatements(Node $node): ?array
    {
        if (!property_exists($node, 'stmts') || !\is_array($node->stmts)) {
            return null;
        }

        $statements = [];
        foreach ($node->stmts as $statement) {
            if (!$statement instanceof Node\Stmt) {
                return null;
            }

            $statements[] = $statement;
        }

        return $statements;
    }

    /**
     * @return null|array{function_name: self::FUNCTION_CURL_SETOPT|self::FUNCTION_CURL_SETOPT_ARRAY, handle_key: string, line: int}
     */
    private function getCurlOptionSetterCall(Node\Stmt $statement): ?array
    {
        if (!$statement instanceof Expression) {
            return null;
        }

        $expr = $statement->expr;
        if ($expr instanceof Assign) {
            $expr = $expr->expr;
        }

        if (!$expr instanceof FuncCall) {
            return null;
        }

        if (!$expr->name instanceof Node\Name) {
            return null;
        }

        $function_name = strtolower($expr->name->toString());
        if (!\in_array($function_name, [self::FUNCTION_CURL_SETOPT, self::FUNCTION_CURL_SETOPT_ARRAY], true)) {
            return null;
        }

        $args = $expr->getArgs();
        if ($args === []) {
            return null;
        }

        return [
            'function_name' => $function_name,
            'handle_key' => $this->printer->prettyPrintExpr($args[0]->value),
            'line' => $statement->getStartLine(),
        ];
    }

    /**
     * @param list<array{function_name: self::FUNCTION_CURL_SETOPT|self::FUNCTION_CURL_SETOPT_ARRAY, handle_key: string, line: int}> $calls
     *
     * @return list<IdentifierRuleError>
     */
    private function getErrorsForCallSequence(array $calls): array
    {
        if (\count($calls) < 2) {
            return [];
        }

        $has_curl_setopt_array = false;
        foreach ($calls as $call) {
            if ($call['function_name'] === self::FUNCTION_CURL_SETOPT_ARRAY) {
                $has_curl_setopt_array = true;

                break;
            }
        }

        $errors = [];
        $first_curl_setopt = true;
        foreach ($calls as $call) {
            if ($call['function_name'] !== self::FUNCTION_CURL_SETOPT) {
                continue;
            }

            if (!$has_curl_setopt_array && $first_curl_setopt) {
                $first_curl_setopt = false;

                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                'Consecutive curl option setters should use a single curl_setopt_array() call.'
            )
                ->identifier('preferCurlSetoptArray')
                ->line($call['line'])
                ->build();

            $first_curl_setopt = false;
        }

        return $errors;
    }
}
