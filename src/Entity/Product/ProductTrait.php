<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Product;

trait ProductTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, name="external_product_id")
     */
    private $externalProductId;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true, name="web_url")
     */
    private $webUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, name="product_type", options={"default": "simple"})
     */
    private $productType = ProductInterface::TYPE_SIMPLE;

    public function getWebUrl(): ?string
    {
        return $this->webUrl;
    }

    public function setWebUrl(string $webUrl): void
    {
        $this->webUrl = $webUrl;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): void
    {
        $this->productType = $productType;
    }

    public function getExternalProductId(): ?string
    {
        return $this->externalProductId;
    }

    public function setExternalProductId(?string $externalProductId): void
    {
        $this->externalProductId = $externalProductId;
    }
}
