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
}
