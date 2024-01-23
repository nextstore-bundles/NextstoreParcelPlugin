<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Entity\Order;

use Nextstore\SyliusParcelPlugin\Entity\Order\OrderItemInterface;
use Nextstore\SyliusParcelPlugin\Entity\Order\OrderItemTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order_item")
 */
class OrderItem extends BaseOrderItem implements OrderItemInterface
{
    use OrderItemTrait;
}
