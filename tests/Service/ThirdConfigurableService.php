<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Service;

use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use jwderoos\Configurable\tests\Entity\InheritedConfiguration;
use jwderoos\Configurable\Trait\ConfigurableServiceTrait;

class ThirdConfigurableService implements ConfigurableServiceInterface
{
    use ConfigurableServiceTrait;

    public static function getConfigurationClass(): string
    {
        return InheritedConfiguration::class;
    }
}
