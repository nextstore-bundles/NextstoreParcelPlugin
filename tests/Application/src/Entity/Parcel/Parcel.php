<?php

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Entity\Parcel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nextstore\SyliusParcelPlugin\Model\Parcel as BaseParcel;
use Doctrine\ORM\Mapping as ORM;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="nextstore_parcel")
 */
class Parcel extends BaseParcel implements ParcelInterface
{
}
