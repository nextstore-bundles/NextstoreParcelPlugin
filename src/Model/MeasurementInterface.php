<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

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
