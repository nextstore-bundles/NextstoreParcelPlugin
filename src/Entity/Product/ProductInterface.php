<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Product;

use Sylius\Component\Core\Model\ProductInterface as BaseProductInterface;

interface ProductInterface extends BaseProductInterface
{
    public const TYPE_SIMPLE = 'simple';

    public const TYPE_MANUAl = 'manual';

    public function getProductType(): string;

    public function setProductType(string $productType): void;

    public function getWebUrl(): ?string;

    public function setWebUrl(string $webUrl): void;
}
