<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Entity\Payment;

use Nextstore\SyliusParcelPlugin\Entity\Payment\PaymentInterface;
use Nextstore\SyliusParcelPlugin\Entity\Payment\PaymentTrait;
use Sylius\Component\Core\Model\Payment as BasePayment;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment")
 * @AssociationOverrides({
 *      @AssociationOverride(name="order",
 *          joinColumns=@ORM\JoinColumn(
 *              name="order_id", referencedColumnName="id", nullable=true
 *          )
 *      )
 * })
 */
class Payment extends BasePayment implements PaymentInterface
{
    use PaymentTrait;
}
