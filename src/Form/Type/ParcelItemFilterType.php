<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Form\Type;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType as SymfonyDateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ParcelItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('phone', TextType::class, [
                'label' => 'sylius.ui.phone_number',
                'required' => false,
                'data' => $options['data']['phone'],
            ])
            ->add('startDate', SymfonyDateType::class, [
                'widget' => 'single_text',
                'label' => 'sylius.ui.start_date',
                'required' => true,
                'data' => $options['data']['from'],
                'constraints' => [
                    new Assert\Date([]),
                    new Assert\NotBlank([]),
                ],
            ])
            ->add('endDate', SymfonyDateType::class, [
                'widget' => 'single_text',
                'label' => 'sylius.ui.end_date',
                'required' => true,
                'data' => $options['data']['to'],
                'constraints' => [
                    new Assert\Date([]),
                    new Assert\NotBlank([]),
                ],
            ])
            ->add('orderBy', ChoiceType::class, [
                'label' => 'sylius.ui.sort',
                'required' => false,
                'data' => $options['data']['orderBy'],
                'choices' => [
                    'Шинэ эхэндээ' => 'ASC',
                    'Хуучин эхэндээ' => 'DESC',
                ],
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'sylius.ui.state',
                'required' => false,
                'data' => $options['data']['state'],
                'choices' => [
                    'sylius.ui.new' => Parcel::STATE_NEW,
                    'nextstore_sylius_parcel.ui.confirmed' => Parcel::STATE_CONFIRMED,
                    'nextstore_sylius_parcel.ui.shipped_to_mongolia' => Parcel::STATE_SHIPPED_TO_MONGOLIA,
                    'nextstore_sylius_parcel.ui.arrived_in_mongolia' => Parcel::STATE_ARRIVED_IN_MONGOLIA,
                    'nextstore_sylius_parcel.ui.shipped_to_customer' => Parcel::STATE_SHIPPED_TO_CUSTOMER,
                    'nextstore_sylius_parcel.ui.delivered' => Parcel::STATE_DELIVERED,
                ],
            ])
            ->add('orderNumber', TextType::class, [
                'label' => 'nextstore_sylius_parcel.ui.order_number',
                'required' => false,
                'data' => $options['data']['orderNumber'],
            ])
            ->add('trackingCode', TextType::class, [
                'label' => 'nextstore_sylius_parcel.ui.tracking_code',
                'required' => false,
                'data' => $options['data']['trackingCode'],
            ])
            ->add('parcelCode', TextType::class, [
                'label' => 'nextstore_sylius_parcel.ui.parcel_code',
                'required' => false,
                'data' => $options['data']['parcelCode'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
