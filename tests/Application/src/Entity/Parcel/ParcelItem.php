<?php

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Entity\Parcel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nextstore\SyliusParcelPlugin\Model\ParcelItem as BaseParcelItem;
use Doctrine\ORM\Mapping as ORM;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelItemInterface;
use Tests\Nextstore\SyliusParcelPlugin\Application\src\Entity\Parcel\Parcel;

/**
 * @ORM\Entity
 * @ORM\Table(name="nextstore_parcel_item")
 */
class ParcelItem extends BaseParcelItem implements ParcelItemInterface
{
}
