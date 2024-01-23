<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $parcel = $menu->addChild('parcel_plugin');

        $parcel
            ->addChild('parcel', ['route' => 'nextstore_sylius_parcel_admin_parcel_index'])
            ->setLabel('nextstore_sylius_parcel.ui.parcel')
            ->setLabelAttribute('icon', 'box')
        ;

        $parcel
            ->addChild('pack', ['route' => 'nextstore_sylius_parcel_admin_order_items_show_for_parcel'])
            ->setLabel('nextstore_sylius_parcel.ui.pack_order_items')
            ->setLabelAttribute('icon', 'boxes')
        ;

        $parcel
            ->addChild('item-control', ['route' => 'nextstore_sylius_parcel_admin_manage_order_items'])
            ->setLabel('nextstore_sylius_parcel.ui.manage_order_items')
            ->setLabelAttribute('icon', 'tasks')
        ;
        $parcel
            ->addChild('parcel-item-control', ['route' => 'nextstore_sylius_parcel_admin_manage_parcel_items'])
            ->setLabel('nextstore_sylius_parcel.ui.manage_parcel_items')
            ->setLabelAttribute('icon', 'tasks')
        ;
    }
}
