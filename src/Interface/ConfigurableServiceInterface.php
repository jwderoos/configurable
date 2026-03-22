<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Interface;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated since 1.3, will be removed in 2.0. Use #[ConfigurableService] attribute on your service class instead.
 *             Implement ConfigurableServiceTrait without this interface for attribute-based configuration.
 */
interface ConfigurableServiceInterface
{
    public static function getConfigurableOptions(): OptionsResolver;

    /** @return class-string<ConfigurableServiceConfigurationInterface> */
    public static function getConfigurationClass(): string;

    public static function supportsConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): bool;
}
