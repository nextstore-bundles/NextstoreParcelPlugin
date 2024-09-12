<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Sylius\Component\Payment\Model\Payment as BasePayment;

class ParcelPayment extends BasePayment implements ParcelPaymentInterface
{
    /** @var Parcel */
    protected ParcelInterface $parcel;

    protected $paidAmount = 0;

    public function getParcel(): ?ParcelInterface
    {
        return $this->parcel;
    }

    public function setParcel(?ParcelInterface $parcel): void
    {
        $this->parcel = $parcel;
    }

    public function getPaidAmount(): ?int
    {
        return $this->paidAmount;
    }

    public function setPaidAmount(int $paidAmount): void
    {
        $this->paidAmount = $paidAmount;
    }
}
