<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use jwderoos\Configurable\Exception\ConfigurationPropertyNotInitializedException;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;

/**
 * @template P of ConfigurableServiceConfigurationPropertyInterface
 */
trait ConfigurationPropertiesTrait
{
    /**
     * @var Collection<string, P>
     */
    protected Collection $properties;

    /**
     * @return class-string<P>
     */
    abstract public function getPropertyClass(): string;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    /**
     * @param Collection<string, P> $properties
     */
    public function setProperties(Collection $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * @return Collection<string, P>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    /**
     * @param P $configurableServiceConfigurationProperty
     */
    public function setProperty(
        ConfigurableServiceConfigurationPropertyInterface $configurableServiceConfigurationProperty
    ): void {
        $configurableServiceConfigurationProperty->setOwner($this);
        $this->properties->set(
            $configurableServiceConfigurationProperty->getName(),
            $configurableServiceConfigurationProperty
        );
    }

    public function prepareConfiguration(ConfigurableServiceInterface $configurableService): void
    {
        $optionsResolver = $configurableService::getConfigurableOptions();
        /** @var class-string<P> $class */
        $class = $this->getPropertyClass();
        foreach ($optionsResolver->getDefinedOptions() as $configurableOption) {
            if (!$this->properties->offsetExists($configurableOption)) {
                $property = new $class();
                $property->setName($configurableOption);
                $this->setProperty($property);
            }
        }
    }

    public function propertyExists(string $propertyName): bool
    {
        return $this->properties->offsetExists($propertyName);
    }

    public function propertyHasValue(string $propertyName): bool
    {
        return (bool) $this->properties->offsetGet($propertyName)?->getValue();
    }

    public function getProperty(string $propertyName): ConfigurableServiceConfigurationPropertyInterface
    {
        $property = $this->properties->offsetGet($propertyName);

        return $property ?? throw new ConfigurationPropertyNotInitializedException($this, $propertyName);
    }

    public function getPropertyValue(string $propertyName): null|string|array
    {
        return $this->getProperty($propertyName)->getValue();
    }

    public function getPropertyValueAsString(string $propertyName): string
    {
        if (
            !$this->propertyExists($propertyName)
        ) {
            throw new ConfigurationPropertyNotInitializedException($this, $propertyName);
        }

        $value = $this->getPropertyValue($propertyName);

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    /**
     * @return mixed[]
     */
    public function getPropertyValueAsArray(string $propertyName): array
    {
        $value = $this->getPropertyValue($propertyName);

        return is_array($value) ? $value : [$value];
    }

    public function isSupported(ConfigurableServiceInterface $configurableService): bool
    {
        return $configurableService::supportsConfiguration($this);
    }

    public function validateConfiguration(ConfigurableServiceInterface $configurableService): bool
    {
        if (!$this->isSupported($configurableService)) {
            return true;
        }

        $requiredOptions = $configurableService::getConfigurableOptions()->getRequiredOptions();
        foreach ($requiredOptions as $requiredOption) {
            if (
                !$this->propertyExists($requiredOption) ||
                !$this->getProperty($requiredOption)->hasValue()
            ) {
                return false;
            }
        }

        return true;
    }
}
