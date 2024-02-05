<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Sylius\Component\Payment\Model\Payment as BasePayment;

class ParcelPayment extends BasePayment implements ParcelPaymentInterface
{
    /** @var Parcel */
    private ParcelInterface $parcel;

    public function getParcel(): ?ParcelInterface
    {
        return $this->parcel;
    }

    public function setParcel(?ParcelInterface $parcel): void
    {
        $this->parcel = $parcel;
    }
}
