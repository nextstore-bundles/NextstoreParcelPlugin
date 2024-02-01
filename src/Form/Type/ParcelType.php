<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Currency\Model\Currency;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ParcelType extends AbstractResourceType
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
            ->addEventSubscriber(new AddCodeFormSubscriber(null, [
                'label' => 'nextstore_sylius_parcel.form.parcel.code',
            ]))
            ->add('channel', ChannelChoiceType::class, [
                'label' => 'sylius.form.product.channels',
            ])
            ->add('notes', TextType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.notes',
            ])
            ->add('currencyCode', ChoiceType::class, [
                'label' => 'nextstore_sylius_parcel.form.parcel.notes',
                'choices' => $currencies,
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
        ;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix(): string
    {
        return 'nextstore_sylius_parcel_parcel';
    }
}
