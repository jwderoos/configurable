<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Service;

use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Trait\ConfigurableServiceTrait;
use jwderoos\Configurable\tests\Entity\Configuration;

#[ConfigurableService(configurationClass: Configuration::class, supportsConfigurationCallback: 'isSupported')]
class ValidSubtypeCallbackService
{
    use ConfigurableServiceTrait;

    public function isSupported(Configuration $configurableServiceConfiguration): bool
    {
        return true;
    }
}
