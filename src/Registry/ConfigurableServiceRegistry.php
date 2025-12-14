<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Registry;

use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use jwderoos\Configurable\Interface\InheritedConfigurableServiceConfigurationInterface;

class ConfigurableServiceRegistry
{
    /** @var ConfigurableServiceInterface[][] */
    private array $servicesByService = [];

    /**
     * @param iterable<ConfigurableServiceInterface> $services
     */
    public function __construct(
        iterable $services
    ) {
        foreach ($services as $service) {
            $class = $service::getConfigurationClass();
            if (!isset($this->servicesByService[$class])) {
                $this->servicesByService[$class] = [];
            }

            $this->servicesByService[$class][$service::class] = $service;
        }
    }

    /**
     * @return ConfigurableServiceInterface[]
     */
    public function getConfigurableServicesByConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): array {
        $class = $configurableServiceConfiguration::class;
        if (str_starts_with($class, 'Proxies\\__CG__\\')) {
            $class = substr($class, strlen('Proxies\\__CG__\\'));
        }

        $services = [];
        if (
            $configurableServiceConfiguration instanceof InheritedConfigurableServiceConfigurationInterface
            && $configurableServiceConfiguration->getParent() instanceof ConfigurableServiceConfigurationInterface
        ) {
            $services = $this->getConfigurableServicesByConfiguration($configurableServiceConfiguration->getParent());
        }

        if (!isset($this->servicesByService[$class])) {
            return $services;
        }

        return array_merge($this->servicesByService[$class], $services);
    }
}
