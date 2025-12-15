<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Exception;

use Exception;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;

class ConfigurationPropertyNotInitializedException extends Exception
{
    public function __construct(
        ConfigurableServiceConfigurationInterface $configuration,
        string $propertyName
    ) {
        parent::__construct(
            sprintf(
                'Property "%s" is not configured in configuration "%s (%d)".',
                $propertyName,
                $configuration::class,
                $configuration->getId(),
            )
        );
    }
}
