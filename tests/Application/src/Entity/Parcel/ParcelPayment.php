<?php

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Entity\Parcel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nextstore\SyliusParcelPlugin\Model\ParcelPayment as BaseParcelPayment;
use Doctrine\ORM\Mapping as ORM;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelPaymentInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="nextstore_parcel_payment")
 */
class ParcelPayment extends BaseParcelPayment implements ParcelPaymentInterface
{
    public const STATE_PARTIALLY_PAID = 'partially_paid';
    public const TRANSITION_PARTIALLY_PAY= 'partially_pay';
}
