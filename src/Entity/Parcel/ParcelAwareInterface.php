<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Parcel;

interface ParcelAwareInterface
{
    public function getParcel(): ?Parcel;

    public function setParcel(?Parcel $parcel): void;
}
