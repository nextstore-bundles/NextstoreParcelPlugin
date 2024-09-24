<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Repository\Parcel;

use Nextstore\SyliusParcelPlugin\Model\ParcelItem;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
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

    public function getItems($state, $phoneNumber, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode, $parcelCode)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin(ParcelItem::class, 'pi', 'WITH', 'p.id = pi.parcel')
            ->leftJoin(OrderItem::class, 'oi', 'WITH', 'oi.id = pi.orderItem')
            ->leftJoin(Order::class, 'o', 'WITH', 'o.id = oi.order')
            ->leftJoin(Customer::class, 'c', 'WITH', 'c.id = p.customer')
        ;
        if ($state) {
            $qb
                ->andWhere('p.state = :state')
                ->setParameter('state', $state)
            ;
        }
        if ($phoneNumber) {
            $qb
                ->andWhere('c.phoneNumber LIKE :phone')
                ->setParameter('phone', '%' . $phoneNumber . '%')
            ;
        }
        if ($startDate && $endDate) {
            $end = new \DateTime($endDate);
            $end = $end->modify('+1 day');

            $qb->andWhere('p.createdAt > :startDate');
            $qb->andWhere('p.createdAt < :endDate');
            $qb->setParameter('startDate', $startDate);
            $qb->setParameter('endDate', $end);
        }
        if ($orderNumber) {
            $qb->andWhere('o.number = :orderNumber');
            $qb->setParameter('orderNumber', $orderNumber);
        }
        if ($trackingCode) {
            $qb->andWhere('pi.trackingCode = :trackingCode');
            $qb->setParameter('trackingCode', $trackingCode);
        }
        if ($parcelCode) {
            $qb->andWhere('p.code = :parcelCode');
            $qb->setParameter('parcelCode', $parcelCode);
        }
        $qb->addOrderBy('o.createdAt', $orderBy);

        return $qb->getQuery()->getResult();
    }
}
