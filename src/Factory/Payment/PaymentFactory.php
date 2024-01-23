<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Factory\Payment;

use Nextstore\SyliusParcelPlugin\Entity\Payment\PaymentInterface as NextstorePaymentInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class PaymentFactory implements PaymentFactoryInterface
{
    /** @var FactoryInterface */
    private $decoratedFactory;

    public function __construct(FactoryInterface $factory)
    {
        $this->decoratedFactory = $factory;
    }

    public function createNew(): NextstorePaymentInterface
    {
        return $this->decoratedFactory->createNew();
    }

    public function createWithAmountAndCurrencyCode(int $amount, string $currency): PaymentInterface
    {
        return $this->decoratedFactory->createWithAmountAndCurrencyCode();
    }
}
