<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface ParcelItemInterface extends
    ResourceInterface,
    TimestampableInterface,
    MeasurementInterface
{
    public function getTotal(): ?int;

    public function setTotal(?int $total): void;

    public function getTrackingCode(): ?string;

    public function setTrackingCode(?string $total): void;

    public function getParcel(): ?ParcelInterface;

    public function setParcel(?ParcelInterface $parcel): void;

    public function getOrderItem(): OrderItemInterface;

    public function setOrderItem(OrderItemInterface $orderItem): void;
}
