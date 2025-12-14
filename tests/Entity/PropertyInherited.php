<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Entity;

use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;
use jwderoos\Configurable\Trait\ConfigurationPropertyTrait;

class PropertyInherited implements ConfigurableServiceConfigurationPropertyInterface
{
    use ConfigurationPropertyTrait;

    /** @use ConfigurableServiceConfigurationInterface<Configuration> */
    private ConfigurableServiceConfigurationInterface $configurableServiceConfiguration;

    public function setOwner(ConfigurableServiceConfigurationInterface $configurableServiceConfiguration): void
    {
        $this->configurableServiceConfiguration = $configurableServiceConfiguration;
    }

    public function getOwner(): ConfigurableServiceConfigurationInterface
    {
        return $this->configurableServiceConfiguration;
    }
}
