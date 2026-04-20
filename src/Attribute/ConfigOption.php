<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Attribute;

use Attribute;
use jwderoos\Configurable\Enum\ConfigOptionType;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class ConfigOption
{
    /**
     * @param ConfigOptionType|null $type          Allowed type. Defaults to ConfigOptionType::String.
     * @param bool                  $required      Whether this option must be set.
     * @param mixed                 $default       Default value (only used when $required is false).
     * @param string|null           $description   Human-readable description shown in OptionsResolver info.
     * @param mixed[]|null          $allowedValues Restricts the option to a fixed set of values.
     */
    public function __construct(
        public ?ConfigOptionType $type = null,
        public bool $required = true,
        public mixed $default = null,
        public ?string $description = null,
        public ?array $allowedValues = null,
    ) {
    }
}
