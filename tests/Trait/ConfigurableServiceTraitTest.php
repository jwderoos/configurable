<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Trait;

use jwderoos\Configurable\tests\Entity\Configuration;
use jwderoos\Configurable\tests\Service\ConfigurableService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

final class ConfigurableServiceTraitTest extends TestCase
{
    public function testGetConfigOptions(): void
    {
        $configurableService = new ConfigurableService();
        $optionsResolver = $configurableService::getConfigurableOptions();
        $this->assertCount(2, $optionsResolver->getDefinedOptions());
    }


    public function testGetConfigurableOptions(): void
    {
        $configuration = new Configuration();
        $configurableService = new ConfigurableService();

        $this->assertFalse($configuration->validateConfiguration($configurableService));

        $class = $configuration->getPropertyClass();
        $property = new $class();
        $property->setName('configurationOption1');

        $configuration->setProperty($property);
        $this->assertTrue($configuration->propertyExists('configurationOption1'));
        $this->assertFalse($configuration->validateConfiguration($configurableService));

        $property->setValue('testValue');

        $property = new $class();
        $property->setName('configurationOption2');
        $property->setValue([]);

        $configuration->setProperty($property);

        $this->assertTrue($configuration->validateConfiguration($configurableService));
    }

    public function testGetConfigurableOptionsInfo(): void
    {
        $configurableService = new ConfigurableService();
        $optionsResolver = $configurableService::getConfigurableOptions();

        $this->assertIsString($optionsResolver->getInfo('configurationOption1'));
        $optionsResolver->setDefault('configurationOption1', 'testValue1');
        $optionsResolver->setDefault('configurationOption2', []);

        $values = $optionsResolver->resolve([]);

        $this->assertEquals('testValue1', $values['configurationOption1']);

        $this->expectException(InvalidOptionsException::class);
        $optionsResolver->resolve(['configurationOption1' => []]);
    }
}
