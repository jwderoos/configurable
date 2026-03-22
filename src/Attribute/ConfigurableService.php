<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Attribute;

use Attribute;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ConfigurableService
{
    /** @param class-string<ConfigurableServiceConfigurationInterface> $configurationClass */
    public function __construct(
        public string $configurationClass,
    ) {
    }
}
