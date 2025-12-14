<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Entity;

use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationPropertyInterface;
use jwderoos\Configurable\Trait\ConfigurationPropertyTrait;

class Property implements ConfigurableServiceConfigurationPropertyInterface
{
    use ConfigurationPropertyTrait;

    /** @use ConfigurableServiceConfigurationInterface<Configuration> */
    private ConfigurableServiceConfigurationInterface $configurableServiceConfiguration;

//    private string $propertyName;
//
//    /** @var mixed[]|string|null  */
//    private array|string|null $propertyValue = null;

//    public function setName(string $name): void
//    {
//        $this->propertyName = $name;
//    }
//
//    public function getName(): string
//    {
//        return $this->propertyName;
//    }

//    /**
//     * @return null|string|mixed[]
//     */
//    public function getValue(): null|array|string
//    {
//        return $this->propertyValue;
//    }
//
//    public function setValue(array|string|null $newValue): void
//    {
//        $this->propertyValue = $newValue;
//    }
//
//    public function hasValue(): bool
//    {
//        return true;
//    }

    public function setOwner(ConfigurableServiceConfigurationInterface $configurableServiceConfiguration): void
    {
        $this->configurableServiceConfiguration = $configurableServiceConfiguration;
    }

    public function getOwner(): ConfigurableServiceConfigurationInterface
    {
        return $this->configurableServiceConfiguration;
    }
}
