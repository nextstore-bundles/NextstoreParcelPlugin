<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Entity\Parcel;

use Doctrine\Common\Collections\Collection;

interface ParcelsAwareInterface
{
    public function getParcels(): ?Collection;

    public function addParcel(?Parcel $parcel): void;

    public function removeParcel(?Parcel $parcel): void;

    public function hasParcel(?Parcel $parcel): bool;
}
