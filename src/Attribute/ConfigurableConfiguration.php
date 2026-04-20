<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Attribute;

use Attribute;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ConfigurableConfiguration
{
    /** @param class-string<ConfigurableServiceConfigurationPropertyInterface> $propertyClass */
    public function __construct(
        public string $propertyClass,
    ) {
    }
}
