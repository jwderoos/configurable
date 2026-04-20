<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LogicException;
use jwderoos\Configurable\Exception\ConfigurationPropertyNotInitializedException;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use jwderoos\Configurable\Registry\ConfigurableServiceRegistry;

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

    /**
     * @deprecated since 1.3, will be removed in 2.0.
     *             Use {@see ConfigurableServiceRegistry::prepareConfiguration()} instead.
     */
    public function prepareConfiguration(object $configurableService): void
    {
        if (!$configurableService instanceof ConfigurableServiceInterface) {
            throw new LogicException(
                $configurableService::class . ' is not a subclass of ' . ConfigurableServiceInterface::class
            );
        }

        ConfigurableServiceRegistry::prepareConfigurationForService($this, $configurableService);
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

        return is_array($value)
            ? implode(', ', array_map(
                static fn (mixed $item): string => is_scalar($item) || $item === null ? (string) $item : '',
                $value
            ))
            : (string) $value;
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

    /**
     * @deprecated since 1.3, will be removed in 2.0.
     *             Use {@see ConfigurableServiceRegistry::validateConfiguration()} instead.
     */
    public function validateConfiguration(ConfigurableServiceInterface $configurableService): bool
    {
        return ConfigurableServiceRegistry::validateConfigurationForService($this, $configurableService);
    }
}
