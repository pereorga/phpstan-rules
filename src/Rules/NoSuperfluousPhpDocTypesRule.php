<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InFunctionNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use function preg_match;
use function sprintf;
use function str_contains;
use function strrpos;
use function substr;
use function trim;

/**
 * Detects @param and @return tags that redundantly duplicate type information already present in native type declarations.
 *
 * @implements Rule<InFunctionNode>
 */
final class NoSuperfluousPhpDocTypesRule implements Rule
{
    public function getNodeType(): string
    {
        return InFunctionNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $functionReflection = $node->getFunctionReflection();
        $docComment = $node->getDocComment();

        if ($docComment === null) {
            return [];
        }

        $errors = [];
        $docCommentText = $docComment->getText();

        // Check @param tags
        foreach ($functionReflection->getVariants()[0]->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $nativeType = $parameter->getNativeType();

            // Skip if no native type (type must be in PHPDoc)
            if ($nativeType === null) {
                continue;
            }

            // Skip complex types that need PHPDoc (arrays, iterables, callables, unions)
            if ($this->isComplexType($nativeType)) {
                continue;
            }

            // Check if there's a redundant @param tag
            $pattern = '/@param\s+([^\s]+)\s+\$' . preg_quote($paramName, '/') . '\b/';
            if (preg_match($pattern, $docCommentText, $matches) === 1) {
                $phpDocType = trim($matches[1]);

                // Skip if PHPDoc has complex type info (generics, arrays with shapes)
                if ($this->hasComplexTypeAnnotation($phpDocType)) {
                    continue;
                }

                // Check if PHPDoc type matches or is redundant with native type
                if ($this->isRedundantWithNativeType($phpDocType, $nativeType)) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            '@param tag for parameter $%s has type %s which is already declared in the signature.',
                            $paramName,
                            $phpDocType
                        )
                    )
                        ->identifier('superfluousPhpDocType.param')
                        ->build();
                }
            }
        }

        // Check @return tag
        $returnType = $functionReflection->getVariants()[0]->getNativeReturnType();
        if ($returnType !== null && !$this->isComplexType($returnType)) {
            $pattern = '/@return\s+([^\s]+)/';
            if (preg_match($pattern, $docCommentText, $matches) === 1) {
                $phpDocType = trim($matches[1]);

                // Skip if PHPDoc has complex type info
                if (!$this->hasComplexTypeAnnotation($phpDocType)) {
                    if ($this->isRedundantWithNativeType($phpDocType, $returnType)) {
                        $errors[] = RuleErrorBuilder::message(
                            sprintf(
                                '@return tag has type %s which is already declared in the signature.',
                                $phpDocType
                            )
                        )
                            ->identifier('superfluousPhpDocType.return')
                            ->build();
                    }
                }
            }
        }

        return $errors;
    }

    private function isComplexType(Type $type): bool
    {
        // Arrays, iterables, callables need PHPDoc for shape/generic information
        if ($type instanceof ArrayType || $type instanceof IterableType || $type instanceof CallableType) {
            return true;
        }

        // Union types need PHPDoc if they're complex
        if ($type instanceof UnionType) {
            return true;
        }

        return false;
    }

    private function hasComplexTypeAnnotation(string $phpDocType): bool
    {
        // Check for array shapes: array{foo: string}
        if (str_contains($phpDocType, '{')) {
            return true;
        }

        // Check for generics: array<string, int>, list<string>
        if (str_contains($phpDocType, '<')) {
            return true;
        }

        // Check for union types with more than just null: string|int
        if (str_contains($phpDocType, '|')) {
            // Allow ?type (which is type|null)
            if (preg_match('/^\?/', $phpDocType) === 1) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function isRedundantWithNativeType(string $phpDocType, Type $nativeType): bool
    {
        $nativeTypeString = $nativeType->describe(\PHPStan\Type\VerbosityLevel::typeOnly());

        // Normalize nullable types
        $phpDocType = preg_replace('/^\?/', '', $phpDocType) ?? $phpDocType;
        $nativeTypeString = preg_replace('/\|null$/', '', $nativeTypeString) ?? $nativeTypeString;

        // Simple types that are redundant
        $simpleTypes = ['string', 'int', 'bool', 'float', 'void', 'mixed', 'never', 'null'];

        foreach ($simpleTypes as $simpleType) {
            if ($phpDocType === $simpleType && str_contains($nativeTypeString, $simpleType)) {
                return true;
            }
        }

        // Check for class types - handle both FQN and short names
        if ($nativeType instanceof ObjectType) {
            $className = $nativeType->getClassName();

            // Check if PHPDoc type matches either the FQN or the short class name
            if ($phpDocType === $className || $phpDocType === '\\' . $className) {
                return true;
            }

            // Check if PHPDoc has short name and native type is FQN
            $lastBackslash = strrpos($className, '\\');
            $shortName = $lastBackslash !== false ? substr($className, $lastBackslash + 1) : $className;
            if ($phpDocType === $shortName) {
                return true;
            }
        }

        // Exact match fallback
        if ($phpDocType === $nativeTypeString) {
            return true;
        }

        return false;
    }
}
