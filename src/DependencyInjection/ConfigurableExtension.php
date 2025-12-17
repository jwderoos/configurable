<?php

declare(strict_types=1);

namespace jwderoos\Configurable\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConfigurableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $yamlFileLoader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $yamlFileLoader->load('services.yaml');
    }
}
