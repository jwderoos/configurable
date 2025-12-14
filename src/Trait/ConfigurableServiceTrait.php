<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use ReflectionClass;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait ConfigurableServiceTrait
{
    /** @return class-string<ConfigurableServiceInterface> */
    abstract public static function getConfigurationClass(): string;

    /**
     * @return string[]
     */
    private static function getConfigOptions(): array
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
        $configOptions = static::getConfigOptions();

        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined($configOptions);

        foreach ($configOptions as $key => $value) {
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
