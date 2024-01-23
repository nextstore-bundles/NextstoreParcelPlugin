<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class NextstoreSyliusParcelPluginExtension extends AbstractResourceExtension
{
    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->registerResources('nextstore_sylius_parcel', $config['driver'], $config['resources'], $container);

        $loader->load('services.xml');
    }
}
