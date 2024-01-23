<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity;

trait MeasurementTrait
{
    /** @var float|null */
    private $width = 0;

    /** @var float|null */
    private $height = 0;

    /** @var float|null */
    private $length = 0;

    /** @var float|null */
    private $weight = 0;

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): void
    {
        $this->weight = $weight;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): void
    {
        $this->height = $height;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): void
    {
        $this->length = $length;
    }
}
