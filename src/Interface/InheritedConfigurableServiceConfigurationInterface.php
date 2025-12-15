<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Interface;

interface InheritedConfigurableServiceConfigurationInterface extends ConfigurableServiceConfigurationInterface
{
    public function getParent(): ?ConfigurableServiceConfigurationInterface;
}
