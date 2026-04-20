<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Resolver;

use ReflectionClass;
use jwderoos\Configurable\Attribute\ConfigOption;
use jwderoos\Configurable\Enum\ConfigOptionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfigOptionResolver
{
    /**
     * Reads all #[ConfigOption]-annotated constants from $reflectionClass into $optionsResolver.
     *
     * Returns the option names (constant values) that were registered so callers can
     * avoid processing them a second time.
     *
     * @param ReflectionClass<object> $reflectionClass
     * @return string[]
     */
    public static function apply(ReflectionClass $reflectionClass, OptionsResolver $optionsResolver): array
    {
        $names = [];
        foreach ($reflectionClass->getReflectionConstants() as $reflectionClassConstant) {
            $attrs = $reflectionClassConstant->getAttributes(ConfigOption::class);
            if ($attrs === []) {
                continue;
            }

            $value = $reflectionClassConstant->getValue();
            if (!is_string($value)) {
                continue;
            }

            /** @var ConfigOption $configOption */
            $configOption = $attrs[0]->newInstance();
            $names[] = $value;
            $optionsResolver->setDefined($value);
            $optionsResolver->setAllowedTypes($value, ($configOption->type ?? ConfigOptionType::String)->value);

            if ($configOption->description !== null) {
                $optionsResolver->setInfo($value, $configOption->description);
            }

            if ($configOption->required) {
                $optionsResolver->setRequired($value);
            } elseif ($configOption->default !== null) {
                $optionsResolver->setDefault($value, $configOption->default);
            }

            if ($configOption->allowedValues !== null) {
                $optionsResolver->setAllowedValues($value, $configOption->allowedValues);
            }
        }

        return $names;
    }
}
