<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Payment;

use Nextstore\SyliusParcelPlugin\Entity\Parcel\ParcelInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface ParcelPaymentInterface extends ResourceInterface, TimestampableInterface
{
    public const STATE_AUTHORIZED = 'authorized';

    public const STATE_CART = 'cart';

    public const STATE_NEW = 'new';

    public const STATE_PROCESSING = 'processing';

    public const STATE_COMPLETED = 'completed';

    public const STATE_FAILED = 'failed';

    public const STATE_CANCELLED = 'cancelled';

    public const STATE_REFUNDED = 'refunded';

    public const STATE_UNKNOWN = 'unknown';

    /**
     * @return PaymentMethodInterface
     */
    public function getMethod(): ?PaymentMethodInterface;

    public function setMethod(?PaymentMethodInterface $method): void;

    public function getState(): ?string;

    public function setState(string $state): void;

    public function getCurrencyCode(): ?string;

    public function setCurrencyCode(string $currencyCode): void;

    public function getAmount(): ?int;

    public function setAmount(int $amount): void;

    public function getDetails(): array;

    public function setDetails(array $details): void;

    public function getParcel(): ?ParcelInterface;

    public function setParcel(?ParcelInterface $parcel): void;
}
