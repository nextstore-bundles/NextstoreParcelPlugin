<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity;

interface MeasurementInterface
{
    public function getWeight(): ?float;

    public function setWeight(?float $weight): void;

    public function getWidth(): ?float;

    public function setWidth(?float $width): void;

    public function getHeight(): ?float;

    public function setHeight(?float $height): void;

    public function getLength(): ?float;

    public function setLength(?float $length): void;
}
