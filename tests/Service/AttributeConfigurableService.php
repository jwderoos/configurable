<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Service;

use jwderoos\Configurable\Attribute\ConfigOption;
use jwderoos\Configurable\Enum\ConfigOptionType;
use jwderoos\Configurable\Attribute\ConfigurableService;
use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use jwderoos\Configurable\Trait\ConfigurableServiceTrait;
use jwderoos\Configurable\tests\Entity\Configuration;

#[ConfigurableService(configurationClass: Configuration::class)]
class AttributeConfigurableService implements ConfigurableServiceInterface
{
    use ConfigurableServiceTrait;

    #[ConfigOption(required: true, description: 'A required string option')]
    public const OPTION_NAME = 'optionName';

    public const NON_CONFIG_CONSTANT = 'ignored';

    #[ConfigOption(type: ConfigOptionType::Array, required: false, default: [])]
    public const OPTION_TAGS = 'optionTags';

    #[ConfigOption(required: false, default: 'low', allowedValues: ['low', 'high'])]
    public const OPTION_PRIORITY = 'optionPriority';

    #[ConfigOption]
    public const OPTION_DEFAULT_REQUIRED = 'optionDefaultRequired';
}
