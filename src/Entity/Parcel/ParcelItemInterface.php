<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Parcel;

use Nextstore\SyliusParcelPlugin\Entity\MeasurementInterface;
use Sylius\Component\Core\Model\OrderItem;
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

    public function getParcel(): ?Parcel;

    public function setParcel(?Parcel $parcel): void;

    public function getOrderItem(): OrderItem;

    public function setOrderItem(OrderItem $orderItem): void;
}
