<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Interface;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableServiceInterface
{
    public static function getConfigurableOptions(): OptionsResolver;

    /** @return class-string<ConfigurableServiceConfigurationInterface> */
    public static function getConfigurationClass(): string;

    public static function supportsConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): bool;
}
