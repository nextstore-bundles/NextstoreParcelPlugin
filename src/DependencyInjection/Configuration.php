<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\DependencyInjection;

use Nextstore\SyliusParcelPlugin\Form\Type\ParcelItemType;
use Nextstore\SyliusParcelPlugin\Form\Type\ParcelType;
use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelItem;
use Nextstore\SyliusParcelPlugin\Model\ParcelItemInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelPayment;
use Nextstore\SyliusParcelPlugin\Model\ParcelPaymentInterface;
use Nextstore\SyliusParcelPlugin\Factory\Payment\ParcelPaymentFactory;
use Nextstore\SyliusParcelPlugin\Repository\Parcel\ParcelItemRepository;
use Nextstore\SyliusParcelPlugin\Repository\Parcel\ParcelRepository;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('nextstore_sylius_parcel');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('nextstore_sylius_parcel');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('driver')->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)->end()
            ->end();

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }


    /**
     * @param ArrayNodeDefinition $node
     */
    private function addResourcesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
            ->arrayNode('resources')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('parcel')
            ->addDefaultsIfNotSet()
            ->children()
            ->variableNode('options')->end()
            ->arrayNode('classes')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(Parcel::class)->cannotBeEmpty()->end()
            ->scalarNode('interface')->defaultValue(ParcelInterface::class)->cannotBeEmpty()->end()
            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
            ->scalarNode('repository')->defaultValue(ParcelRepository::class)->cannotBeEmpty()->end()
            ->scalarNode('factory')->defaultValue(Factory::class)->end()
            ->scalarNode('form')->defaultValue(ParcelType::class)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('parcel_item')
            ->addDefaultsIfNotSet()
            ->children()
            ->variableNode('options')->end()
            ->arrayNode('classes')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(ParcelItem::class)->cannotBeEmpty()->end()
            ->scalarNode('interface')->defaultValue(ParcelItemInterface::class)->cannotBeEmpty()->end()
            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
            ->scalarNode('repository')->defaultValue(ParcelItemRepository::class)->cannotBeEmpty()->end()
            ->scalarNode('factory')->defaultValue(Factory::class)->end()
            ->scalarNode('form')->defaultValue(ParcelItemType::class)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('parcel_payment')
            ->addDefaultsIfNotSet()
            ->children()
            ->variableNode('options')->end()
            ->arrayNode('classes')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(ParcelPayment::class)->cannotBeEmpty()->end()
            ->scalarNode('interface')->defaultValue(ParcelPaymentInterface::class)->cannotBeEmpty()->end()
            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
            ->scalarNode('factory')->defaultValue(Factory::class)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }
}
