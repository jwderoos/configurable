<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Entity;

use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Trait\ConfigurationPropertiesTrait;

class Configuration implements ConfigurableServiceConfigurationInterface
{
    /** @use ConfigurationPropertiesTrait<Property> */
    use ConfigurationPropertiesTrait;

    public function getId(): ?int
    {
        return null;
    }

    /**
     * @return class-string<Property>
     */
    public function getPropertyClass(): string
    {
        return Property::class;
    }
}
