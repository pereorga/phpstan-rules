<?php

declare(strict_types=1);

namespace PereOrga\PHPStanRules\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InFunctionNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

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
            // When no type hint exists, PHPStan returns MixedType or null
            if ($nativeType === null || $nativeType instanceof MixedType) {
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
                        \sprintf(
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
        // When no return type hint exists, PHPStan returns MixedType or null
        if ($returnType !== null && !($returnType instanceof MixedType) && !$this->isComplexType($returnType)) {
            $pattern = '/@return\s+([^\s]+)/';
            if (preg_match($pattern, $docCommentText, $matches) === 1) {
                $phpDocType = trim($matches[1]);

                // Skip if PHPDoc has complex type info
                if (!$this->hasComplexTypeAnnotation($phpDocType)) {
                    if ($this->isRedundantWithNativeType($phpDocType, $returnType)) {
                        $errors[] = RuleErrorBuilder::message(
                            \sprintf(
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
        if ($type->isArray()->yes() || $type->isIterable()->yes() || $type->isCallable()->yes()) {
            return true;
        }

        // Union types need PHPDoc if they're complex
        if ($type instanceof UnionType) {
            // Simple nullable types (Type|null or ?Type) are not complex
            $types = $type->getTypes();
            if (\count($types) === 2) {
                $hasNull = false;
                $otherType = null;

                foreach ($types as $innerType) {
                    if ($innerType->isNull()->yes()) {
                        $hasNull = true;
                    } else {
                        $otherType = $innerType;
                    }
                }

                // If it's Type|null and the other type is simple, treat as not complex
                if ($hasNull && $otherType !== null && !$this->isComplexType($otherType)) {
                    return false;
                }
            }

            // All other union types are complex (e.g., string|int)
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

        // Check for union types
        if (str_contains($phpDocType, '|')) {
            // Allow ?type (which is type|null)
            if (preg_match('/^\?/', $phpDocType) === 1) {
                return false;
            }

            // Allow simple nullable unions: type|null or null|type
            if (preg_match('/^([^|]+)\|null$/i', $phpDocType) === 1 || preg_match('/^null\|([^|]+)$/i', $phpDocType) === 1) {
                return false;
            }

            // All other union types are complex (e.g., string|int)
            return true;
        }

        return false;
    }

    private function isRedundantWithNativeType(string $phpDocType, Type $nativeType): bool
    {
        $nativeTypeString = $nativeType->describe(VerbosityLevel::typeOnly());

        // Normalize nullable types - strip both ?prefix and |null suffix from both sides
        $phpDocType = preg_replace('/^\?/', '', $phpDocType) ?? $phpDocType;
        $phpDocType = preg_replace('/\|null$/i', '', $phpDocType) ?? $phpDocType;
        $phpDocType = preg_replace('/^null\|/i', '', $phpDocType) ?? $phpDocType;

        $nativeTypeString = preg_replace('/\|null$/i', '', $nativeTypeString) ?? $nativeTypeString;
        $nativeTypeString = preg_replace('/^null\|/i', '', $nativeTypeString) ?? $nativeTypeString;

        // Simple types that are redundant
        $simpleTypes = ['string', 'int', 'bool', 'float', 'void', 'mixed', 'never', 'null'];

        foreach ($simpleTypes as $simpleType) {
            if ($phpDocType === $simpleType && str_contains($nativeTypeString, $simpleType)) {
                return true;
            }
        }

        // Check for class types - handle both FQN and short names
        if ($nativeType->isObject()->yes()) {
            $classNames = $nativeType->getObjectClassNames();
            foreach ($classNames as $className) {
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
        }

        // Exact match fallback
        if ($phpDocType === $nativeTypeString) {
            return true;
        }

        return false;
    }
}
