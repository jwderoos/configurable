<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Interface;

use Doctrine\Common\Collections\Collection;

interface ConfigurableServiceConfigurationInterface
{
    public function getId(): ?int;

    public function prepareConfiguration(ConfigurableServiceInterface $configurableService): void;

    public function validateConfiguration(ConfigurableServiceInterface $configurableService): bool;

    /**
     * @return Collection<string, mixed>
     */
    public function getProperties(): Collection;

    public function propertyExists(string $propertyName): bool;

    public function propertyHasValue(string $propertyName): bool;

    public function setProperty(
        ConfigurableServiceConfigurationPropertyInterface $configurableServiceConfigurationProperty
    ): void;

    public function getProperty(string $propertyName): ConfigurableServiceConfigurationPropertyInterface;

    /** @return class-string<ConfigurableServiceConfigurationPropertyInterface> */
    public function getPropertyClass(): string;

    /** @return null|string|mixed[] */
    public function getPropertyValue(string $propertyName): null|string|array;

    public function getPropertyValueAsString(string $propertyName): string;

    /** @return string[] */
    public function getPropertyValueAsArray(string $propertyName): array;

    public function isSupported(ConfigurableServiceInterface $configurableService): bool;
}
