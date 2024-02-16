<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Validator;

use Nextstore\SyliusParcelPlugin\Model\OrderItemStates;
use Doctrine\ORM\EntityManagerInterface;
use Nextstore\SyliusDropshippingCorePlugin\Model\OrderItemInterface;
use Sylius\Component\Order\Model\Order;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Webmozart\Assert\Assert;

class ValidatorOrderItem
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param mixed $itemId
     */
    public function validateEditOrderItem($itemId): OrderItemInterface
    {
        $item = $this->entityManager->getRepository(OrderItemInterface::class)->find($itemId);
        if (!$item instanceof OrderItemInterface) {
            throw new NotFoundResourceException('Order item not found');
        }

        $order = $item->getOrder();
        if ($order->getState() === Order::STATE_CANCELLED) {
            throw new \Exception('Can\t edit item when Order is cancelled');
        }
        if ($order->getState() === Order::STATE_FULFILLED) {
            throw new \Exception('Can\t edit item when Order is fulfilled');
        }

        return $item;
    }

    public function validateInputTransition(string $transition): void
    {
        $validTransitions = [
            OrderItemStates::TRANSITION_CONFIRM,
            OrderItemStates::TRANSITION_PURCHASE,
            OrderItemStates::TRANSITION_FOREIGN_DELIVERY,
            OrderItemStates::TRANSITION_RESTORE,
            OrderItemStates::TRANSITION_CANCEL,
        ];

        Assert::inArray($transition, $validTransitions);
    }
}
