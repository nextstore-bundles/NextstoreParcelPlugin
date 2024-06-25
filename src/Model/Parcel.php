<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Nextstore\SyliusParcelPlugin\Model\MeasurementTrait;
use Nextstore\SyliusParcelPlugin\Model\ParcelPaymentInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Webmozart\Assert\Assert;

class Parcel implements ParcelInterface
{
    public const STATE_NEW = 'new';

    public const STATE_CONFIRMED = 'confirmed';

    public const STATE_SHIPPED_TO_HOMELAND = 'shipped_to_homeland';

    public const STATE_ARRIVED_IN_HOMELAND = 'arrived_in_homeland';

    public const STATE_SHIPPED_TO_CUSTOMER = 'shipped_to_customer';

    public const STATE_DELIVERED = 'delivered';

    public const GRAPH_PARCEL = 'nextstore_parcel';

    public const TRANSITION_CONFIRM = 'confirm';

    public const TRANSITION_SHIP_TO_HOMELAND = 'ship_to_homeland';

    public const TRANSITION_ARRIVED_IN_HOMELAND = 'arrived_in_homeland';

    public const TRANSITION_SHIP_TO_CUSTOMER = 'ship_to_customer';

    public const TRANSITION_DELIVER = 'deliver';

    use TimestampableTrait;
    use MeasurementTrait;

    protected ?int $id = null;

    protected ?string $code = null;

    protected ?int $itemsTotal = 0;

    protected ?int $total = 0;

    protected ?int $quantity = 0;

    protected ?string $state;

    protected ?string $currencyCode;

    protected ?string $notes;

    /** @var AddressInterface|null */
    protected ?AddressInterface $address;

    /** @var Collection|ParcelItemInterface[] */
    protected  $items;

    /** @var ChannelInterface|null */
    protected ?ChannelInterface $channel;

    /** @var CustomerInterface|null */
    protected ?CustomerInterface $customer;

    /** @var Collection|ParcelPaymentInterface[] */
    protected $payments;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        /** @var ArrayCollection<array-key, OrderItem> $this->items */
        $this->items = new ArrayCollection();
        /** @var ArrayCollection<array-key, ParcelPaymentInterface> $this->payments */
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getItemsTotal(): ?int
    {
        return $this->itemsTotal;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
        $this->recalculatePaymentTotal();
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getItems(): ?Collection
    {
        return $this->items;
    }

    public function addItem(ParcelItemInterface $item): void
    {
        if ($this->hasItem($item)) {
            return;
        }
        ++$this->quantity;
        $this->itemsTotal += $item->getTotal();
        $this->items->add($item);
        $item->setParcel($this);

        $this->recalculateTotal();
    }

    public function hasItem(ParcelItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    public function removeItem(ParcelItemInterface $item): void
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $this->itemsTotal -= $item->getTotal();
            $this->recalculateTotal();
            $item->setParcel(null);
            --$this->quantity;
        }
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    public function getPayments(): ?Collection
    {
        return $this->payments;
    }

    public function hasPayments(): bool
    {
        if ($this->payments === null) {
            return false;
        }
        return !$this->payments->isEmpty();
    }

    public function addPayment(ParcelPaymentInterface $payment): void
    {
        Assert::isInstanceOf($payment, ParcelPaymentInterface::class);

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setParcel($this);
        }
    }

    public function removePayment(ParcelPaymentInterface $payment): void
    {
        Assert::isInstanceOf($payment, ParcelPaymentInterface::class);

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setParcel(null);
        }
    }

    public function hasPayment(ParcelPaymentInterface $payment): bool
    {
        return $this->payments->contains($payment);
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(?string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->itemsTotal;

        if ($this->total < 0) {
            $this->total = 0;
        }
        $this->recalculatePaymentTotal();
    }

    public function recalculatePaymentTotal(): void
    {
        if ($this->hasPayments()) {
            /** @var ParcelPaymentInterface $payment */
            $payment = $this->payments[0];
            $payment->setAmount($this->total);
        }
    }

    public function recalculateItemsTotal(): void
    {
        $this->itemsTotal = 0;
        foreach ($this->items as $item) {
            $this->itemsTotal += $item->getTotal();
        }

        $this->recalculateTotal();
    }

    public function getAddress(): ?AddressInterface
    {
        return $this->address;
    }

    public function setAddress(AddressInterface $address): void
    {
        $this->address = $address;
    }

    public function getLastPayment(?string $state = null): ?ParcelPaymentInterface
    {
        if ($this->payments === null) {
            return null;
        }
        if ($this->payments->isEmpty()) {
            return null;
        }

        $payment = $this->payments->filter(function (ParcelPaymentInterface $payment) use ($state): bool {
            return null === $state || $payment->getState() === $state;
        })->last();

        return $payment !== false ? $payment : null;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function setItemsTotal(?int $itemsTotal): void
    {
        $this->itemsTotal = $itemsTotal;
    }
}
