<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Payment;

use Nextstore\SyliusParcelPlugin\Entity\Parcel\ParcelAwareInterface;
use Sylius\Component\Core\Model\PaymentInterface as BasePaymentInterface;

interface PaymentInterface extends BasePaymentInterface, ParcelAwareInterface
{
    public function getAmountMnt(): int;

    public function setAmountMnt(?int $amountMnt): void;
}
