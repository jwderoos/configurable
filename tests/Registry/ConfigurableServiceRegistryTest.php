<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Registry;

use jwderoos\Configurable\tests\Entity\InheritedConfiguration;
use jwderoos\Configurable\tests\Entity\UnsupportedConfiguration;
use jwderoos\Configurable\tests\Service\ConfigurableService;
use jwderoos\Configurable\tests\Entity\Configuration;
use jwderoos\Configurable\tests\Service\OtherConfigurableService;
use jwderoos\Configurable\tests\Service\ThirdConfigurableService;
use PHPUnit\Framework\TestCase;
use jwderoos\Configurable\Registry\ConfigurableServiceRegistry;

final class ConfigurableServiceRegistryTest extends TestCase
{
    private ConfigurableService $configurableService;

    private OtherConfigurableService $otherConfigurableService;

    private ThirdConfigurableService $thirdConfigurableService;

    private ConfigurableServiceRegistry $configurableServiceRegistry;

    protected function setUp(): void
    {
        $this->configurableService = new ConfigurableService();
        $this->otherConfigurableService = new OtherConfigurableService();
        $this->thirdConfigurableService = new ThirdConfigurableService();
        $this->configurableServiceRegistry = new ConfigurableServiceRegistry([
            $this->configurableService,
            $this->otherConfigurableService,
            $this->thirdConfigurableService,
        ]);
    }

    public function testConfigurableServiceRegistry(): void
    {
        $services = $this->configurableServiceRegistry->getConfigurableServicesByConfiguration(new Configuration());
        $this->assertEquals(
            [
                $this->configurableService::class => $this->configurableService,
                $this->otherConfigurableService::class => $this->otherConfigurableService,
            ],
            $services,
        );
    }

    public function testGetConfigurableServiceInherited(): void
    {
        $inheritedConfiguration = new InheritedConfiguration();
        $inheritedConfiguration->setParentConfig(new Configuration());

        $services = $this->configurableServiceRegistry->getConfigurableServicesByConfiguration($inheritedConfiguration);
        $this->assertEquals(
            [
                $this->configurableService::class => $this->configurableService,
                $this->otherConfigurableService::class => $this->otherConfigurableService,
                $this->thirdConfigurableService::class => $this->thirdConfigurableService,
            ],
            $services,
        );
    }

    public function testGetConfigurableServiceInheritedWithoutOwnService(): void
    {
        $this->configurableServiceRegistry = new ConfigurableServiceRegistry([
            $this->configurableService,
            $this->otherConfigurableService,
        ]);

        $inheritedConfiguration = new InheritedConfiguration();
        $inheritedConfiguration->setParentConfig(new Configuration());

        $services = $this->configurableServiceRegistry->getConfigurableServicesByConfiguration($inheritedConfiguration);
        $this->assertEquals(
            [
                $this->configurableService::class => $this->configurableService,
                $this->otherConfigurableService::class => $this->otherConfigurableService,
            ],
            $services,
        );
    }

    public function testNoConfigurableService(): void
    {
        $this->assertEmpty(
            $this->configurableServiceRegistry->getConfigurableServicesByConfiguration(new UnsupportedConfiguration())
        );
    }

    public function testPrepareConfigurationCreatesProperties(): void
    {
        $configuration = new Configuration();

        $this->configurableServiceRegistry->prepareConfiguration($configuration);

        $this->assertTrue($configuration->propertyExists('configurationOption1'));
        $this->assertTrue($configuration->propertyExists('configurationOption2'));
    }

    public function testPrepareConfigurationIsIdempotent(): void
    {
        $configuration = new Configuration();

        $this->configurableServiceRegistry->prepareConfiguration($configuration);
        $this->configurableServiceRegistry->prepareConfiguration($configuration);

        $this->assertCount(2, $configuration->getProperties());
    }

    public function testPrepareConfigurationWithInheritedPreparesParentAndChild(): void
    {
        $configuration = new Configuration();
        $inheritedConfiguration = new InheritedConfiguration();
        $inheritedConfiguration->setParentConfig($configuration);

        $this->configurableServiceRegistry->prepareConfiguration($inheritedConfiguration);

        $this->assertNotEmpty($configuration->getProperties());
        $this->assertNotEmpty($inheritedConfiguration->getProperties());
    }

    public function testValidateConfigurationReturnsFalseWhenRequiredOptionMissing(): void
    {
        $configuration = new Configuration();

        $this->assertFalse($this->configurableServiceRegistry->validateConfiguration($configuration));
    }

    public function testValidateConfigurationReturnsTrueWhenAllRequiredOptionsPresent(): void
    {
        $configuration = new Configuration();
        $this->configurableServiceRegistry->prepareConfiguration($configuration);
        $configuration->getProperty('configurationOption1')->setValue('value1');
        $configuration->getProperty('configurationOption2')->setValue(['value2']);

        $this->assertTrue($this->configurableServiceRegistry->validateConfiguration($configuration));
    }

    public function testValidateConfigurationReturnsTrueForUnsupportedConfiguration(): void
    {
        $this->assertTrue(
            $this->configurableServiceRegistry->validateConfiguration(new UnsupportedConfiguration())
        );
    }
}
