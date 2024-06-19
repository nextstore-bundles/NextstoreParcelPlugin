<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Currency\Model\Currency;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ParcelItemType extends AbstractResourceType
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currencies = $this->em->getRepository(Currency::class)
            ->createQueryBuilder('c')
            ->select('c.code')
            ->getQuery()
            ->getScalarResult();
        $builder
            ->add('trackingCode', TextType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.tracking_code',
                'required' => true,
            ])
            ->add('width', NumberType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.width',
            ])
            ->add('height', NumberType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.height',
            ])
            ->add('length', NumberType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.length',
            ])
            ->add('weight', NumberType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.weight',
            ])
            ->add('total', MoneyType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.total',
                // 'currency' => $options['channel']->getBaseCurrency()->getCode(),
                'currency' => 'MNT',
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix(): string
    {
        return 'nextstore_sylius_parcel_parcel_item';
    }
}
