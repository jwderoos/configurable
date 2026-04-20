<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use LogicException;
use ReflectionClass;
use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Resolver\ConfigOptionResolver;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 */
trait ConfigurableServiceTrait
{
    /**
     * @deprecated since 1.3, will be removed in 2.0.
     *             Use #[ConfigurableService] attribute on your service class instead.
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

    /** @deprecated since 1.3, will be removed in 2.0. Use #[ConfigOption] attributes on class constants instead. */
    public static function getConfigurableOptions(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $reflectionClass = new ReflectionClass(static::class);

        // Collect attribute-based options (#[ConfigOption] on class constants).
        $attributeOptionNames = ConfigOptionResolver::apply($reflectionClass, $optionsResolver);

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

    /**
     * @deprecated since 1.3, will be removed in 2.0.
     *             Use #[ConfigurableService] attribute on your service class instead.
     *             Implement ConfigurableServiceTrait without this interface for attribute-based configuration.
     */
    public static function supportsConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): bool {
        $class = self::getConfigurationClass();

        return $configurableServiceConfiguration instanceof $class;
    }
}
