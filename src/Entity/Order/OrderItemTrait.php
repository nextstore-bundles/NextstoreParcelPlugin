<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Order;

trait OrderItemTrait
{
    /** @ORM\Column(type="string", nullable=true) */
    private $state = OrderItemStates::STATE_NEW;

    /** @ORM\Column(type="string", nullable=true) */
    private $description;

    /** @ORM\Column(type="string", nullable=true, length=50) */
    private $color;

    /** @ORM\Column(type="string", nullable=true, length=50) */
    private $size;

    /** @ORM\Column(type="string", length=255, nullable=true, name="tracking_code") */
    private $trackingCode;

    /** @ORM\Column(type="string", length=255, nullable=true, name="external_product_code") */
    private $externalProductCode;

    /** @ORM\Column(type="text", nullable=true, name="web_url") */
    private $webUrl;

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): void
    {
        $this->size = $size;
    }

    public function getTrackingCode(): ?string
    {
        return $this->trackingCode;
    }

    public function setTrackingCode(?string $trackingCode): void
    {
        $this->trackingCode = $trackingCode;
    }

    public function getExternalProductCode(): ?string
    {
        return $this->externalProductCode;
    }

    public function setExternalProductCode(?string $externalProductCode): void
    {
        $this->externalProductCode = $externalProductCode;
    }

    public function getWebUrl(): ?string
    {
        return $this->webUrl;
    }

    public function setWebUrl(string $webUrl): void
    {
        $this->webUrl = $webUrl;
    }
}
