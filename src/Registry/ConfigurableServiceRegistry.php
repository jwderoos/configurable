<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Registry;

use LogicException;
use ReflectionClass;
use jwderoos\Configurable\Attribute\ConfigurableConfiguration;
use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use jwderoos\Configurable\Interface\InheritedConfigurableServiceConfigurationInterface;

class ConfigurableServiceRegistry
{
    /** @var object[][] */
    private array $servicesByService = [];

    /**
     * @param iterable<object> $services
     */
    public function __construct(
        iterable $services
    ) {
        foreach ($services as $service) {
            $class = $this->resolveConfigurationClassForService($service);
            if (!isset($this->servicesByService[$class])) {
                $this->servicesByService[$class] = [];
            }

            $this->servicesByService[$class][$service::class] = $service;
        }
    }

    /**
     * @return object[]
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
        object $configurableService
    ): void {
        /** @var ConfigurableServiceInterface $configurableService */
        $optionsResolver = $configurableService::getConfigurableOptions();
        $class = self::resolvePropertyClass($configurableServiceConfiguration);
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
        object $configurableService
    ): bool {
        /** @var ConfigurableServiceInterface $configurableService */
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
     * Resolves the configuration class a service supports.
     *
     * Checks ConfigurableServiceInterface first (interface-based flow), then falls
     * back to reading the #[ConfigurableService] attribute (attribute-based flow).
     *
     * @return class-string
     */
    private function resolveConfigurationClassForService(object $service): string
    {
        $reflectionClass = new ReflectionClass($service);
        $attributes = $reflectionClass->getAttributes(ConfigurableService::class);
        if ($attributes !== []) {
            /** @var ConfigurableService $attr */
            $attr = $attributes[0]->newInstance();

            return $attr->configurationClass;
        }

        if ($service instanceof ConfigurableServiceInterface) {
            return $service::getConfigurationClass();
        }

        throw new LogicException(sprintf(
            'Service "%s" must implement ConfigurableServiceInterface'
                . ' or carry #[ConfigurableService(configurationClass: ...)] attribute.',
            $service::class
        ));
    }

    /**
     * Resolves the property class a configuration uses.
     *
     * Reads #[ConfigurableConfiguration(propertyClass: ...)] attribute first (attribute-based
     * flow), then falls back to getPropertyClass() (interface-based flow).
     *
     * @return class-string<ConfigurableServiceConfigurationPropertyInterface>
     */
    private static function resolvePropertyClass(
        ConfigurableServiceConfigurationInterface $configurableServiceConfiguration
    ): string {
        $reflectionClass = new ReflectionClass($configurableServiceConfiguration);
        $attributes = $reflectionClass->getAttributes(ConfigurableConfiguration::class);
        if ($attributes !== []) {
            /** @var ConfigurableConfiguration $attr */
            $attr = $attributes[0]->newInstance();

            return $attr->propertyClass;
        }

        return $configurableServiceConfiguration->getPropertyClass();
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
