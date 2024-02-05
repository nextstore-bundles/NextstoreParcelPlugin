<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class QPayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('qpay_client_id', TextType::class, [
                'label' => 'Client id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.gateway_config.stripe.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('qpay_client_secret', TextType::class, [
                'label' => 'Client secret',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.gateway_config.stripe.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('endpoint', TextType::class, [
                'label' => 'QPay endpoint',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.gateway_config.stripe.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('qpay_template_id', TextType::class, [
                'label' => 'Template id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.gateway_config.stripe.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('qpay_merchant_id', TextType::class, [
                'label' => 'Merchant id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.gateway_config.stripe.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('qpay_invoice_description', TextType::class, [
                'label' => 'Invoice description',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.gateway_config.stripe.secret_key.not_blank',
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('token', HiddenType::class)
            ->add('tokenExpiredAt', HiddenType::class);
    }
}
