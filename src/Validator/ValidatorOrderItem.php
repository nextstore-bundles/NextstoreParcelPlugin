<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Validator;

use Nextstore\SyliusParcelPlugin\Entity\Order\OrderItemInterface;
use Nextstore\SyliusParcelPlugin\Entity\Order\OrderItemStates;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @param array<int,mixed> $array
     */
    public function validateAddToCartManually(array $array): void
    {
        $productName = (isset($array['productName']) && !empty($array['productName'])) ? $array['productName'] : null;
        $price = (isset($array['price']) && !empty($array['price'])) ? (float) $array['price'] : null;
        $quantity = (isset($array['quantity']) && !empty($array['quantity'])) ? $array['quantity'] : null;
        $size = (isset($array['size']) && !empty($array['size'])) ? $array['size'] : null;
        $color = (isset($array['color']) && !empty($array['color'])) ? $array['color'] : null;
        $description = (isset($array['description']) && !empty($array['description'])) ? $array['description'] : null;
        $webUrl = (isset($array['webUrl']) && !empty($array['webUrl'])) ? $array['webUrl'] : null;

        Assert::notNull($webUrl);
        Assert::notNull($productName);
        Assert::notNull($price);
        Assert::notNull($size);
        Assert::notNull($color);
        Assert::notNull($quantity);
        Assert::integer($quantity);
        Assert::float($price);
    }
}
