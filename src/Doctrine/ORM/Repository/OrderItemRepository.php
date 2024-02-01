<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Doctrine\ORM\Repository;

use Nextstore\SyliusParcelPlugin\Model\OrderItemStates;
use Nextstore\SyliusParcelPlugin\Model\ParcelItem;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderItemRepository as BaseOrderItemRepository;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\OrderCheckoutStates;

// use Nextstore\SyliusParcelPlugin\Doctrine\ORM\Repository\OrderItemRepositoryInterface as NextstoreOrderItemRepository;

class OrderItemRepository extends BaseOrderItemRepository
{
    public function getPackableItems($phone, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode): array
    {
        $sql = '
            SELECT
            c.phone_number as phone,
            c.email as email,
            i.id as id,
            i.product_name as productName,
            i.variant_name as variantName,
            i.external_product_code as productCode,
            i.state as state,
            i.unit_price as unitPrice,
            i.total as total,
            i.quantity as quantity,
            i.color as color,
            i.size as size,
            img.path as imagePath,
            img.type as imageType,
            o.currency_code as currencyCode,
            c.id as customerId,
            o.shipping_address_id as addressId,
            i.tracking_code as trackingCode,
            o.number as orderNumber,
            o.id as orderId,
            o.checkout_completed_at as date
            FROM `sylius_order_item` i
            LEFT JOIN `sylius_order` o ON o.id = i.order_id
            LEFT JOIN `sylius_customer` c ON c.id = o.customer_id
            LEFT JOIN `nextstore_parcel_item` pi ON pi.order_item_id = i.id
            LEFT JOIN `sylius_product_variant` pv ON pv.id = i.variant_id
            LEFT JOIN `sylius_product` p ON pv.product_id = p.id
            LEFT JOIN `sylius_product_image` img ON img.owner_id = p.id
            WHERE pi.order_item_id IS NULL
            AND i.state = :state';

        if ($phone) {
            $sql .= ' AND c.phone_number LIKE :phone';
        }
        if ($startDate && $endDate) {
            $sql .= ' AND o.checkout_completed_at BETWEEN :startDate AND :endDate';
        }
        if ($orderNumber) {
            $sql .= ' AND o.number = :orderNumber';
        }
        if ($trackingCode) {
            $sql .= ' AND i.tracking_code = :trackingCode';
        }

        $sql .= ' GROUP BY
            c.phone_number,
            c.email,
            i.id,
            i.product_name,
            i.variant_name,
            i.external_product_code,
            i.state,
            i.unit_price,
            i.total,
            i.quantity,
            i.color,
            i.size,
            img.path,
            img.type,
            o.currency_code,
            c.id,
            o.shipping_address_id,
            i.tracking_code,
            o.number,
            o.id,
            o.checkout_completed_at';

        if ($orderBy === 'DESC') {
            $sql .= ' ORDER BY o.checkout_completed_at DESC';
        } elseif ($orderBy === 'ASC') {
            $sql .= ' ORDER BY o.checkout_completed_at ASC';
        }

        $conn = $this->getEntityManager()->getConnection();
        $statement = $conn->prepare($sql);

        $statement->bindValue('state', OrderItemStates::STATE_FOREIGN_DELIVERY_COMPLETED);
        $phone && $statement->bindValue('phone', '%' . $phone . '%');
        $orderNumber && $statement->bindValue('orderNumber', $orderNumber);
        $trackingCode && $statement->bindValue('trackingCode', $trackingCode);
        if ($startDate && $endDate) {
            $statement->bindValue('startDate', $startDate);
            $statement->bindValue('endDate', $endDate);
        }

        $resultSet = $statement->executeQuery();
        $result = $resultSet->fetchAllAssociative();

        return $result;
    }

    public function getNotPackedItemsForParcel(Customer $customer): mixed
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.order', 'o')
            ->leftJoin('o.customer', 'c')
            ->andWhere('c.id = :customerId')
            ->setParameter('customerId', $customer->getId())
            ->leftJoin(ParcelItem::class, 'pi', 'WITH', 'pi.orderItem = i.id')
            ->andWhere('pi.orderItem  IS NULL')
            ->andWhere('i.state = :state')
            ->setParameter('state', OrderItemStates::STATE_FOREIGN_DELIVERY_COMPLETED)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param mixed $state
     * @param mixed $phoneNumber
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $orderBy
     * @param mixed $orderNumber
     * @param mixed $trackingCode
     */
    public function getItemsWithTrackingCode($state, $phoneNumber, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode): mixed
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin(Order::class, 'o', 'WITH', 'o.id = i.order')
            ->leftJoin(Customer::class, 'c', 'WITH', 'c.id = o.customer')
            ->andWhere('i.trackingCode IS NOT NULL')
            ->andWhere('o.checkoutState = :checkoutState')
            ->setParameter('checkoutState', OrderCheckoutStates::STATE_COMPLETED)
        ;
        if ($state) {
            $qb
                ->andWhere('i.state = :state')
                ->setParameter('state', $state)
            ;
        }
        if ($phoneNumber) {
            $qb
                ->andWhere('c.phoneNumber = :phone')
                ->setParameter('phone', $phoneNumber)
            ;
        }
        if ($startDate && $endDate) {
            $end = new \DateTime($endDate);
            $end = $end->modify('+1 day');

            $qb->andWhere('o.checkoutCompletedAt > :startDate');
            $qb->andWhere('o.checkoutCompletedAt < :endDate');
            $qb->setParameter('startDate', $startDate);
            $qb->setParameter('endDate', $end);
        }
        if ($orderNumber) {
            $qb->andWhere('o.number = :orderNumber');
            $qb->setParameter('orderNumber', $orderNumber);
        }
        if ($trackingCode) {
            $qb->andWhere('i.trackingCode = :tCode');
            $qb->setParameter('tCode', $trackingCode);
        }
        $qb->addOrderBy('o.checkoutCompletedAt', $orderBy);

        return $qb->getQuery()->getResult();
    }
}
