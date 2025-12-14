<?php

declare(strict_types=1);

namespace Trait;

use jwderoos\Configurable\Exception\ConfigurationPropertyNotInitializedException;
use jwderoos\Configurable\tests\Entity\Configuration;
use PHPUnit\Framework\TestCase;

final class ConfigurationPropertiesTraitTest extends TestCase
{
    public function testConfigurationNotInitializedException(): void
    {
        $this->expectException(ConfigurationPropertyNotInitializedException::class);
        $this->expectExceptionMessage(
            'Property "configurationOption1" is not configured in configuration'
        );

        $configuration = new Configuration();
        $configuration->getProperty('configurationOption1');
    }
}
