<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Sylius\Component\Payment\Model\Payment as BasePayment;

class ParcelPayment extends BasePayment implements ParcelPaymentInterface
{
    // use TimestampableTrait;

    // protected ?int $id = null;

    /** @var Parcel */
    private ParcelInterface $parcel;

    // /** @var PaymentMethodInterface|null */
    // protected ?PaymentMethodInterface $method;

    // /** @var string|null */
    // protected $currencyCode;

    // /** @var int */
    // protected $amount = 0;

    // /** @var string|null */
    // protected $state = ParcelPaymentInterface::STATE_CART;

    // /** @var mixed[] */
    // protected $details = [];

    // public function __construct()
    // {
    //     $this->createdAt = new \DateTime();
    // }

    // public function getId(): ?int
    // {
    //     return $this->id;
    // }

    // public function getMethod(): ?PaymentMethodInterface
    // {
    //     return $this->method;
    // }

    // public function setMethod(?PaymentMethodInterface $method): void
    // {
    //     $this->method = $method;
    // }

    // public function getCurrencyCode(): ?string
    // {
    //     return $this->currencyCode;
    // }

    // public function setCurrencyCode(string $currencyCode): void
    // {
    //     $this->currencyCode = $currencyCode;
    // }

    // public function getAmount(): ?int
    // {
    //     return $this->amount;
    // }

    // public function setAmount(int $amount): void
    // {
    //     $this->amount = $amount;
    // }

    // public function getState(): ?string
    // {
    //     return $this->state;
    // }

    // public function setState(string $state): void
    // {
    //     $this->state = $state;
    // }

    // public function getDetails(): array
    // {
    //     return $this->details;
    // }

    // public function setDetails(array $details): void
    // {
    //     $this->details = $details;
    // }

    public function getParcel(): ?ParcelInterface
    {
        return $this->parcel;
    }

    public function setParcel(?ParcelInterface $parcel): void
    {
        $this->parcel = $parcel;
    }
}
