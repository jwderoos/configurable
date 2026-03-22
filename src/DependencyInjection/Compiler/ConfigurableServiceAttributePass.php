<?php

declare(strict_types=1);

namespace jwderoos\Configurable\DependencyInjection\Compiler;

use ReflectionClass;
use jwderoos\Configurable\Attribute\ConfigurableService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurableServiceAttributePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if ($class === null) {
                continue;
            }

            if (!class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            if ($reflectionClass->getAttributes(ConfigurableService::class) === []) {
                continue;
            }

            $definition->addTag('jwderoos.configurable.service');
        }
    }
}
