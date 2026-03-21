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
        $class = $this->resolveClass($configurableServiceConfiguration);

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

    public function prepareConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): void {
        if (
            $configurableServiceConfiguration instanceof InheritedConfigurableServiceConfigurationInterface
            && $configurableServiceConfiguration->getParent() instanceof ConfigurableServiceConfigurationInterface
        ) {
            $this->prepareConfiguration($configurableServiceConfiguration->getParent());

            // Create override slots on the child for all parent services
            $parentClass = $this->resolveClass($configurableServiceConfiguration->getParent());
            foreach ($this->servicesByService[$parentClass] ?? [] as $service) {
                self::prepareConfigurationForService($configurableServiceConfiguration, $service);
            }
        }

        $class = $this->resolveClass($configurableServiceConfiguration);
        foreach ($this->servicesByService[$class] ?? [] as $service) {
            self::prepareConfigurationForService($configurableServiceConfiguration, $service);
        }
    }

    public function validateConfiguration(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): bool {
        $class = $this->resolveClass($configurableServiceConfiguration);
        foreach ($this->servicesByService[$class] ?? [] as $service) {
            if (!self::validateConfigurationForService($configurableServiceConfiguration, $service)) {
                return false;
            }
        }

        return true;
    }

    public static function prepareConfigurationForService(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration,
        ConfigurableServiceInterface $configurableService
    ): void {
        $optionsResolver = $configurableService::getConfigurableOptions();
        $class = $configurableServiceConfiguration->getPropertyClass();
        $localProperties = $configurableServiceConfiguration->getProperties();
        foreach ($optionsResolver->getDefinedOptions() as $option) {
            if (!$localProperties->offsetExists($option)) {
                $property = new $class();
                $property->setName($option);
                $configurableServiceConfiguration->setProperty($property);
            }
        }
    }

    public static function validateConfigurationForService(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration,
        ConfigurableServiceInterface $configurableService
    ): bool {
        if (!$configurableService::supportsConfiguration($configurableServiceConfiguration)) {
            return true;
        }

        foreach ($configurableService::getConfigurableOptions()->getRequiredOptions() as $option) {
            $hasValue = $configurableServiceConfiguration->propertyExists($option)
                && $configurableServiceConfiguration->getProperty($option)->hasValue();
            if (!$hasValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return class-string
     */
    private function resolveClass(ConfigurableServiceConfigurationInterface $configurableServiceConfiguration): string
    {
        $class = $configurableServiceConfiguration::class;
        if (!str_starts_with($class, 'Proxies\\__CG__\\')) {
            return $class;
        }

        /** @var class-string */
        return substr($class, strlen('Proxies\\__CG__\\'));
    }
}
