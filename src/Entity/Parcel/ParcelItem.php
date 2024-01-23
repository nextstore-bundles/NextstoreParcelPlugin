<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Parcel;

use Nextstore\SyliusParcelPlugin\Entity\MeasurementTrait;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Resource\Model\TimestampableTrait;

class ParcelItem implements ParcelItemInterface
{
    use TimestampableTrait;
    use MeasurementTrait;

    protected ?int $id = null;

    private ?int $total = 0;

    private ?string $trackingCode;

    /** @var Parcel|null */
    private $parcel;

    /** @var OrderItem|null */
    private $orderItem;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParcel(): ?Parcel
    {
        return $this->parcel;
    }

    public function setParcel(?Parcel $parcel): void
    {
        $this->parcel = $parcel;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getTrackingCode(): ?string
    {
        return $this->trackingCode;
    }

    public function setTrackingCode(?string $trackingCode): void
    {
        $this->trackingCode = $trackingCode;
    }

    public function getOrderItem(): OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(OrderItem $orderItem): void
    {
        $this->orderItem = $orderItem;
    }
}
