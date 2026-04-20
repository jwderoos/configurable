<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Enum;

enum ConfigOptionType: string
{
    case String = 'string';
    case Array = 'array';
}
