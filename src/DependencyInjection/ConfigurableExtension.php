<?php

declare(strict_types=1);

namespace jwderoos\Configurable\DependencyInjection;

use jwderoos\Configurable\Interface\ConfigurableServiceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConfigurableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ConfigurableServiceInterface::class)
            ->addTag('jwderoos.configurable.service');

        $yamlFileLoader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $yamlFileLoader->load('services.yaml');
    }
}
