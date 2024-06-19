<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Repository\Parcel;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ParcelRepository extends EntityRepository implements RepositoryInterface
{
    public function findByParcelCode(?string $phrase = null, ?string $warehouse = null, ?int $limit = 20)
    {
        $qb = $this->createQueryBuilder('p');
        if ($phrase) {
            $qb->andWhere('p.code LIKE :phrase')
                ->setParameter('phrase', '%' . $phrase . '%');
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
