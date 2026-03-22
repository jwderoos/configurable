<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use LogicException;
use ReflectionClass;
use jwderoos\Configurable\Attribute\ConfigOption;
use jwderoos\Configurable\Enum\ConfigOptionType;
use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait ConfigurableServiceTrait
{
    /**
     * Returns the configuration class this service supports.
     *
     * When the class carries #[ConfigurableService], the configuration class is
     * read from the attribute. Override this method when you cannot (or prefer
     * not to) use the attribute.
     *
     * @return class-string<ConfigurableServiceConfigurationInterface>
     */
    public static function getConfigurationClass(): string
    {
        $reflectionClass = new ReflectionClass(static::class);
        $attributes = $reflectionClass->getAttributes(ConfigurableService::class);

        if ($attributes !== []) {
            /** @var ConfigurableService $attr */
            $attr = $attributes[0]->newInstance();

            return $attr->configurationClass;
        }

        throw new LogicException(sprintf(
            'Class "%s" must either carry #[ConfigurableService(configurationClass: ...)]'
                . ' or override getConfigurationClass().',
            static::class
        ));
    }

    /**
     * Returns CONFIG_* constants defined on the class, keyed by constant name.
     *
     * @deprecated since 1.3, will be removed in 2.0. Use #[ConfigOption] attributes on class constants instead.
     *
     * @return string[]
     */
    protected static function getConfigOptions(): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        $constants = $reflectionClass->getConstants();

        $options = [];
        foreach ($constants as $key => $value) {
            if (str_starts_with($key, 'CONFIG_') && is_string($value)) {
                $options[$key] = $value;
            }
        }

        return $options;
    }

    public static function getConfigurableOptions(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();

        $reflectionClass = new ReflectionClass(static::class);

        // Collect attribute-based options (#[ConfigOption] on class constants).
        $attributeOptionNames = [];
        foreach ($reflectionClass->getReflectionConstants() as $reflectionClassConstant) {
            $attributes = $reflectionClassConstant->getAttributes(ConfigOption::class);
            if ($attributes === []) {
                continue;
            }

            $value = $reflectionClassConstant->getValue();
            if (!is_string($value)) {
                continue;
            }

            /** @var ConfigOption $configOption */
            $configOption = $attributes[0]->newInstance();
            $attributeOptionNames[] = $value;

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

        // Fall back to CONFIG_* convention for constants not covered by an attribute.
        foreach (static::getConfigOptions() as $key => $value) {
            if (in_array($value, $attributeOptionNames, true)) {
                continue;
            }

            $optionsResolver->setDefined($value);
            $optionsResolver->setAllowedTypes(
                $value,
                str_contains((string) $key, '_ARRAY_') ? 'array' : 'string'
            );
            $optionsResolver->setInfo($value, $key);

            if (!str_contains((string) $key, '_OPTIONAL_')) {
                $optionsResolver->setRequired($value);
            }
        }

        return $optionsResolver;
    }

    public static function supportsConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): bool {
        $class = self::getConfigurationClass();

        return $configurableServiceConfiguration instanceof $class;
    }
}
