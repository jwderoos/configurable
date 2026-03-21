<?php

declare(strict_types=1);

namespace jwderoos\Configurable;

use Override;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @todo change inheritance to AbstractBundle to drop 5.4 support */
class ConfigurableBundle extends Bundle
{
    #[Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
