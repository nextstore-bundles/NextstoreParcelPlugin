<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Order;

interface OrderItemStates
{
    public const STATE_NEW = 'new';

    public const STATE_CONFIRMED = 'confirmed';

    public const STATE_CANCELLED = 'cancelled';

    public const STATE_PURCHASED = 'purchased';

    public const STATE_FOREIGN_DELIVERY_COMPLETED = 'foreign_delivery_completed';

    public const GRAPH_ORDER_ITEM = 'sylius_order_item';

    public const TRANSITION_CONFIRM = 'confirm';

    public const TRANSITION_PURCHASE = 'purchase';

    public const TRANSITION_FOREIGN_DELIVERY = 'foreign_delivery';

    public const TRANSITION_RESTORE = 'restore';

    public const TRANSITION_CANCEL = 'cancel';
}
