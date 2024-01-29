<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Factory\Payment;

use Nextstore\SyliusParcelPlugin\Entity\Payment\ParcelPaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ParcelPaymentFactory implements FactoryInterface
{
    /** @var FactoryInterface */
    private $decoratedFactory;

    public function __construct(FactoryInterface $factory)
    {
        $this->decoratedFactory = $factory;
    }

    public function createNew(): ParcelPaymentInterface
    {
        return $this->decoratedFactory->createNew();
    }
}
