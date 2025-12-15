<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Entity;

use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\InheritedConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Trait\InheritedConfigurationPropertiesTrait;

class InheritedConfiguration implements InheritedConfigurableServiceConfigurationInterface
{
    /** @use InheritedConfigurationPropertiesTrait<Property> */
    use InheritedConfigurationPropertiesTrait;

    private ?Configuration $configuration = null;

    public function getId(): ?int
    {
        return null;
    }

    public function setParentConfig(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getParentConfig(): ?Configuration
    {
        return $this->configuration;
    }

    public function getPropertyClass(): string
    {
        return PropertyInherited::class;
    }

    public function getParent(): ?ConfigurableServiceConfigurationInterface
    {
        return $this->configuration;
    }
}
