<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use jwderoos\Configurable\Exception\ConfigurationPropertyNotInitializedException;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;

/**
 * @template P of ConfigurableServiceConfigurationPropertyInterface
 */
trait InheritedConfigurationPropertiesTrait
{
    /** @use ConfigurationPropertiesTrait<P> */
    use ConfigurationPropertiesTrait {
        prepareConfiguration as private basePrepareConfiguration;
        propertyExists as private basePropertyExists;
    }

    abstract public function getParent(): ?ConfigurableServiceConfigurationInterface;

    public function isSupported(ConfigurableServiceInterface $configurableService): bool
    {
        if ($configurableService::supportsConfiguration($this)) {
            return true;
        }

        return $this->getParent() ? $this->getParent()->isSupported($configurableService) : false;
    }

    public function propertyExists(string $propertyName): bool
    {
        if ($this->basePropertyExists($propertyName)) {
            return true;
        }

        return $this->getParent() ? $this->getParent()->propertyExists($propertyName) : false;
    }

    public function getProperty(string $propertyName): ConfigurableServiceConfigurationPropertyInterface
    {
        $property = $this->properties->offsetGet($propertyName);

        if (
            !$property?->hasValue() &&
            $this->getParent() instanceof ConfigurableServiceConfigurationInterface &&
            $this->getParent()->propertyExists($propertyName)
        ) {
            return $this->getParent()->getProperty($propertyName);
        }

        return $property ?? throw new ConfigurationPropertyNotInitializedException($this, $propertyName);
    }

    /**
     * @param P $configurableServiceConfigurationProperty
     */
    public function setProperty(
        ConfigurableServiceConfigurationPropertyInterface $configurableServiceConfigurationProperty
    ): void {
        $propertyClass = self::getPropertyClass();
        if (
            !$configurableServiceConfigurationProperty instanceof $propertyClass &&
            $this->getParent() instanceof ConfigurableServiceConfigurationInterface
        ) {
            $parentPropertyClass = $this->getParent()->getPropertyClass();
            if ($configurableServiceConfigurationProperty instanceof $parentPropertyClass) {
                $this->getParent()->setProperty($configurableServiceConfigurationProperty);
            }

            return;
        }

        $configurableServiceConfigurationProperty->setOwner($this);
        $this->properties->set(
            $configurableServiceConfigurationProperty->getName(),
            $configurableServiceConfigurationProperty
        );
    }

    public function prepareConfiguration(ConfigurableServiceInterface $configurableService): void
    {
        if (
            $this->getParent() instanceof ConfigurableServiceConfigurationInterface
        ) {
            $this->getParent()->prepareConfiguration($configurableService);
        }

        $this->basePrepareConfiguration($configurableService);
    }
}
