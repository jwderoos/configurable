<?php

declare(strict_types=1);

namespace jwderoos\Configurable\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;

/**
 * Validates that when #[ConfigurableService(supportsConfigurationCallback: 'method')] is used:
 * - The method exists on the class
 * - It accepts exactly one parameter typed as ConfigurableServiceConfigurationInterface (or subtype)
 * - It declares a bool return type
 *
 * @implements Rule<Class_>
 */
final readonly class ConfigurableServiceCallbackRule implements Rule
{
    private const string EXPECTED_PARAM_TYPE = ConfigurableServiceConfigurationInterface::class;

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!isset($node->namespacedName)) {
            return [];
        }

        $className = $node->namespacedName->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $nativeReflection = $classReflection->getNativeReflection();
        $attributes = $nativeReflection->getAttributes(ConfigurableService::class);
        if ($attributes === []) {
            return [];
        }

        /** @var ConfigurableService $attr */
        $attr = $attributes[0]->newInstance();
        $callbackMethodName = $attr->supportsConfigurationCallback;
        if ($callbackMethodName === null) {
            return [];
        }

        $errors = [];

        if (!$classReflection->hasMethod($callbackMethodName)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Class %s declares supportsConfigurationCallback: "%s" but that method does not exist.',
                $className,
                $callbackMethodName,
            ))->build();

            return $errors;
        }

        $extendedMethodReflection = $classReflection->getMethod($callbackMethodName, $scope);
        $variant = $extendedMethodReflection->getVariants()[0];
        $parameters = $variant->getParameters();

        if (count($parameters) !== 1) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Method %s::%s() must accept exactly one parameter of type %s.',
                $className,
                $callbackMethodName,
                self::EXPECTED_PARAM_TYPE,
            ))->build();
        } else {
            $paramType = $parameters[0]->getType();
            $isCompatible = false;
            foreach ($paramType->getReferencedClasses() as $referencedClass) {
                if ($referencedClass === self::EXPECTED_PARAM_TYPE) {
                    $isCompatible = true;
                    break;
                }

                if (
                    $this->reflectionProvider->hasClass($referencedClass)
                    && $this->reflectionProvider
                        ->getClass($referencedClass)
                        ->implementsInterface(self::EXPECTED_PARAM_TYPE)
                ) {
                    $isCompatible = true;
                    break;
                }
            }

            if (!$isCompatible) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Parameter of %s::%s() must be typed as %s (or a subtype), got "%s".',
                    $className,
                    $callbackMethodName,
                    self::EXPECTED_PARAM_TYPE,
                    $paramType->describe(VerbosityLevel::typeOnly()),
                ))->build();
            }
        }

        $returnType = $variant->getReturnType();
        if (!$returnType->isBoolean()->yes()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Method %s::%s() must declare a bool return type, got "%s".',
                $className,
                $callbackMethodName,
                $returnType->describe(VerbosityLevel::typeOnly()),
            ))->build();
        }

        return $errors;
    }
}
