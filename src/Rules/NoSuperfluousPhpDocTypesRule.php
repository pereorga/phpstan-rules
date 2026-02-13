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
        $function_reflection = $node->getFunctionReflection();
        $doc_comment = $node->getDocComment();

        if ($doc_comment === null) {
            return [];
        }

        $errors = [];
        $doc_comment_text = $doc_comment->getText();

        // Check @param tags
        foreach ($function_reflection->getVariants()[0]->getParameters() as $parameter) {
            $param_name = $parameter->getName();
            $native_type = $parameter->getNativeType();

            // Skip if no native type (type must be in PHPDoc)
            // When no type hint exists, PHPStan returns MixedType or null
            if ($native_type === null || $native_type instanceof MixedType) {
                continue;
            }

            // Skip complex types that need PHPDoc (arrays, iterables, callables, unions)
            if ($this->isComplexType($native_type)) {
                continue;
            }

            // Check if there's a redundant @param tag
            $pattern = '/@param\s+([^\s]+)\s+\$' . preg_quote($param_name, '/') . '\b/';
            if (preg_match($pattern, $doc_comment_text, $matches) === 1) {
                $php_doc_type = trim($matches[1]);

                // Skip if PHPDoc has complex type info (generics, arrays with shapes)
                if ($this->hasComplexTypeAnnotation($php_doc_type)) {
                    continue;
                }

                // Check if PHPDoc type matches or is redundant with native type
                if ($this->isRedundantWithNativeType($php_doc_type, $native_type)) {
                    $errors[] = RuleErrorBuilder::message(
                        \sprintf(
                            '@param tag for parameter $%s has type %s which is already declared in the signature.',
                            $param_name,
                            $php_doc_type
                        )
                    )
                        ->identifier('superfluousPhpDocType.param')
                        ->build();
                }
            }
        }

        // Check @return tag
        $return_type = $function_reflection->getVariants()[0]->getNativeReturnType();
        // When no return type hint exists, PHPStan returns MixedType or null
        if ($return_type !== null && !($return_type instanceof MixedType) && !$this->isComplexType($return_type)) {
            $pattern = '/@return\s+([^\s]+)/';
            if (preg_match($pattern, $doc_comment_text, $matches) === 1) {
                $php_doc_type = trim($matches[1]);

                // Skip if PHPDoc has complex type info
                if (!$this->hasComplexTypeAnnotation($php_doc_type)) {
                    if ($this->isRedundantWithNativeType($php_doc_type, $return_type)) {
                        $errors[] = RuleErrorBuilder::message(
                            \sprintf(
                                '@return tag has type %s which is already declared in the signature.',
                                $php_doc_type
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
                $has_null = false;
                $other_type = null;

                foreach ($types as $inner_type) {
                    if ($inner_type->isNull()->yes()) {
                        $has_null = true;
                    } else {
                        $other_type = $inner_type;
                    }
                }

                // If it's Type|null and the other type is simple, treat as not complex
                if ($has_null && $other_type !== null && !$this->isComplexType($other_type)) {
                    return false;
                }
            }

            // All other union types are complex (e.g., string|int)
            return true;
        }

        return false;
    }

    private function hasComplexTypeAnnotation(string $php_doc_type): bool
    {
        // Check for array shapes: array{foo: string}
        if (str_contains($php_doc_type, '{')) {
            return true;
        }

        // Check for generics: array<string, int>, list<string>
        if (str_contains($php_doc_type, '<')) {
            return true;
        }

        // Check for union types
        if (str_contains($php_doc_type, '|')) {
            // Allow ?type (which is type|null)
            if (preg_match('/^\?/', $php_doc_type) === 1) {
                return false;
            }

            // Allow simple nullable unions: type|null or null|type
            if (preg_match('/^([^|]+)\|null$/i', $php_doc_type) === 1 || preg_match('/^null\|([^|]+)$/i', $php_doc_type) === 1) {
                return false;
            }

            // All other union types are complex (e.g., string|int)
            return true;
        }

        return false;
    }

    private function isRedundantWithNativeType(string $php_doc_type, Type $native_type): bool
    {
        $native_type_string = $native_type->describe(VerbosityLevel::typeOnly());

        // Normalize nullable types - strip both ?prefix and |null suffix from both sides
        $normalized_doc_type = preg_replace('/^\?/', '', $php_doc_type) ?? $php_doc_type;
        $normalized_doc_type = preg_replace('/\|null$/i', '', $normalized_doc_type) ?? $normalized_doc_type;
        $normalized_doc_type = preg_replace('/^null\|/i', '', $normalized_doc_type) ?? $normalized_doc_type;

        $native_type_string = preg_replace('/\|null$/i', '', $native_type_string) ?? $native_type_string;
        $native_type_string = preg_replace('/^null\|/i', '', $native_type_string) ?? $native_type_string;

        // Simple types that are redundant
        $simple_types = ['string', 'int', 'bool', 'float', 'void', 'mixed', 'never', 'null'];

        foreach ($simple_types as $simple_type) {
            if ($normalized_doc_type === $simple_type && str_contains($native_type_string, $simple_type)) {
                return true;
            }
        }

        // Check for class types - handle both FQN and short names
        if ($native_type->isObject()->yes()) {
            $class_names = $native_type->getObjectClassNames();
            foreach ($class_names as $class_name) {
                // Check if PHPDoc type matches either the FQN or the short class name
                if ($normalized_doc_type === $class_name || $normalized_doc_type === '\\' . $class_name) {
                    return true;
                }

                // Check if PHPDoc has short name and native type is FQN
                $last_backslash = strrpos($class_name, '\\');
                $short_name = $last_backslash !== false ? substr($class_name, $last_backslash + 1) : $class_name;
                if ($normalized_doc_type === $short_name) {
                    return true;
                }
            }
        }

        // Exact match fallback
        if ($normalized_doc_type === $native_type_string) {
            return true;
        }

        return false;
    }
}
