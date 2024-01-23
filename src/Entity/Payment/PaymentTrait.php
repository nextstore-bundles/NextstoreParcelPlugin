<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Payment;

use Nextstore\SyliusParcelPlugin\Entity\Parcel\Parcel;

trait PaymentTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Nextstore\SyliusParcelPlugin\Entity\Parcel\Parcel", inversedBy="payments", cascade={"remove"})
     *
     * @ORM\JoinColumn(name="parcel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $parcel;

    /** @ORM\Column(type="integer", name="amount_mnt", nullable=true) */
    private $amountMnt;

    public function getParcel(): ?Parcel
    {
        return $this->parcel;
    }

    public function setParcel(?Parcel $parcel): void
    {
        $this->parcel = $parcel;
    }

    public function getAmountMnt(): int
    {
        return $this->amountMnt ?? 0;
    }

    public function setAmountMnt(?int $amountMnt): void
    {
        $this->amountMnt = $amountMnt;
    }
}
