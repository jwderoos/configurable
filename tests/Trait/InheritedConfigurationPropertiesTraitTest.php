<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Trait;

use jwderoos\Configurable\tests\Entity\Configuration;
use jwderoos\Configurable\tests\Entity\InheritedConfiguration;
use jwderoos\Configurable\tests\Entity\Property;
use jwderoos\Configurable\tests\Entity\PropertyInherited;
use jwderoos\Configurable\tests\Service\ConfigurableService;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class InheritedConfigurationPropertiesTraitTest extends TestCase
{
    public function testInheritedConfigurationPropertiesWithoutParent(): void
    {
        $inheritedConfiguration = new InheritedConfiguration();
        $configurableService = new ConfigurableService();

        $inheritedConfiguration->prepareConfiguration($configurableService);

        $properties = $inheritedConfiguration->getProperties();
        $this->assertNotEmpty($properties->toArray());
        $this->arrayHasKey('configurationOption1')->evaluate($properties->toArray());

        $property = $properties->current();
        $this->assertInstanceOf(PropertyInherited::class, $property);

        $this->assertEquals($inheritedConfiguration, $property->getOwner());
    }

    public function testInheritedConfigurationPropertiesWithParent(): void
    {
        $inheritedConfiguration = new InheritedConfiguration();
        $configuration = new Configuration();
        $inheritedConfiguration->setParentConfig($configuration);

        $configurableService = new ConfigurableService();

        $inheritedConfiguration->prepareConfiguration($configurableService);

        $this->assertNotEmpty($inheritedConfiguration->getProperties());
        $this->assertInstanceOf(Configuration::class, $inheritedConfiguration->getParentConfig());
        $this->arrayHasKey('configurationOption1')
            ->evaluate($inheritedConfiguration->getProperties()->toArray());
        $this->arrayHasKey('configurationOption1')
            ->evaluate(
                $inheritedConfiguration->getParentConfig()
                ->getProperties()
                ->toArray()
            );
    }

    public function testSettingInheritedConfigurationOnParentOnly(): Property
    {
        $inheritedConfiguration = new InheritedConfiguration();
        $configuration = new Configuration();
        $inheritedConfiguration->setParentConfig($configuration);

        $property = new Property();
        $property->setName('configurationOption1');
        $property->setValue('TestValue');

        $inheritedConfiguration->setProperty($property);

        $this->assertContains($property, $configuration->getProperties()->toArray());
        $this->assertNotContains($property, $inheritedConfiguration->getProperties()->toArray());

        return $property;
    }

    #[Depends('testSettingInheritedConfigurationOnParentOnly')]
    public function testOwner(Property $property): void
    {
        $this->assertInstanceOf(Configuration::class, $property->getOwner());
    }
}
