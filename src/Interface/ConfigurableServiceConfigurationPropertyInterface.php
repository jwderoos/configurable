<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Interface;

interface ConfigurableServiceConfigurationPropertyInterface
{
    public function setName(string $name): void;

    public function getName(): string;

    /**
     * @return null|mixed[]|string
     */
    public function getValue(): null|array|string;

    /**
     * @param null|mixed[]|string $newValue
     */
    public function setValue(null|array|string $newValue): void;

    public function hasValue(): bool;

    public function setOwner(ConfigurableServiceConfigurationInterface $configurableServiceConfiguration): void;

    public function getOwner(): ConfigurableServiceConfigurationInterface;
}
