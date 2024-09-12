<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Sylius\Component\Payment\Model\PaymentInterface;

interface ParcelPaymentInterface extends PaymentInterface
{
    public function getParcel(): ?ParcelInterface;
    public function setParcel(?ParcelInterface $parcel): void;

    public function getPaidAmount(): ?int;
    public function setPaidAmount(int $paidAmount): void;
}
