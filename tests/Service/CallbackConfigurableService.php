<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Service;

use jwderoos\Configurable\Attribute\ConfigOption;
use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Interface\ConfigurableServiceConfigurationInterface;
use jwderoos\Configurable\Trait\ConfigurableServiceTrait;
use jwderoos\Configurable\tests\Entity\Configuration;

#[ConfigurableService(configurationClass: Configuration::class, supportsConfigurationCallback: 'isSupported')]
class CallbackConfigurableService
{
    use ConfigurableServiceTrait;

    #[ConfigOption(required: true)]
    public const CONFIG_REQUIRED_OPTION = 'callbackRequiredOption';

    public function __construct(private bool $supported = true)
    {
    }

    public function isSupported(ConfigurableServiceConfigurationInterface $configurableServiceConfiguration): bool
    {
        return $this->supported;
    }
}
