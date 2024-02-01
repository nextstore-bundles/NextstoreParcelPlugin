<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface ParcelInterface extends
    ResourceInterface,
    CodeAwareInterface,
    TimestampableInterface,
    MeasurementInterface
{
    public function getTotal(): ?int;

    public function setTotal(?int $total): void;

    public function getItemsTotal(): ?int;

    public function setItemsTotal(?int $itemsTotal): void;

    public function getQuantity(): ?int;

    public function setQuantity(?int $quantity): void;

    public function getCurrencyCode(): ?string;

    public function setCurrencyCode(?string $currencyCode): void;

    public function getNotes(): ?string;

    public function setNotes(?string $notes): void;

    public function getState(): ?string;

    public function setState(?string $notes): void;

    public function getItems(): ?Collection;

    public function addItem(ParcelItemInterface $item): void;

    public function hasItem(ParcelItemInterface $item): bool;

    public function removeItem(ParcelItemInterface $item): void;

    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): void;

    public function getPayments(): ?Collection;

    public function hasPayments(): bool;

    public function addPayment(ParcelPaymentInterface $payment): void;

    public function removePayment(ParcelPaymentInterface $payment): void;

    public function hasPayment(ParcelPaymentInterface $payment): bool;

    public function getAddress(): ?AddressInterface;

    public function setAddress(AddressInterface $address): void;

    public function getLastPayment(?string $state = null): ?ParcelPaymentInterface;

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function recalculateTotal(): void;

    public function recalculatePaymentTotal(): void;

    public function recalculateItemsTotal(): void;
}
