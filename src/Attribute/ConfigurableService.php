<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Attribute;

use Attribute;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ConfigurableService
{
    /**
     * @param class-string<ConfigurableServiceConfigurationInterface> $configurationClass
     * @param string|null $supportsConfigurationCallback Method name on this class to call to determine
     *                                                   whether the service supports a given configuration.
     *                                                   The method must accept a single
     *                                                   ConfigurableServiceConfigurationInterface argument
     *                                                   and return bool.
     */
    public function __construct(
        public string $configurationClass,
        public ?string $supportsConfigurationCallback = null,
    ) {
    }
}
