<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Service;

use jwderoos\Configurable\tests\Entity\Configuration;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use jwderoos\Configurable\Trait\ConfigurableServiceTrait;

class ConfigurableService implements ConfigurableServiceInterface
{
    use ConfigurableServiceTrait;

    public const CONFIG_OPTION_1 = 'configurationOption1';

    public const CONFIG_OPTION_ARRAY_2 = 'configurationOption2';

    public const OTHER_NON_CONFIG_CONSTANT = 'TestValue';

    public static function getConfigurationClass(): string
    {
        return Configuration::class;
    }
}
