<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Api;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Doctrine\ORM\EntityManagerInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelPayment;
use Nextstore\SyliusParcelPlugin\Model\ParcelPaymentInterface;
use Sylius\Bundle\ApiBundle\Exception\PaymentNotFoundException;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class ParcelController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function selectPaymentMethod(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedException();
        }
        Assert::keyExists($request->attributes->all(), 'id');
        $parcelId = $request->attributes->get('id');
        Assert::notEmpty($parcelId);
        Assert::keyExists($request->attributes->all(), 'paymentId');
        $paymentId = $request->attributes->get('paymentId');
        Assert::notEmpty($paymentId);

        if ($request->request->all()) {
            $params = $request->request->all();
        } else {
            $params = json_decode($request->getContent(), true);
        }
        Assert::keyExists($params, 'paymentMethod');
        Assert::notEmpty($params['paymentMethod']);

        $paymentMethod = $this->entityManager->getRepository(PaymentMethodInterface::class)->findOneBy(['code' => $params['paymentMethod']]);

        /** @var Parcel $parcel */
        $parcel = $this->entityManager->getRepository(ParcelInterface::class)->find($parcelId);

        /** @var ParcelPayment $payment */
        $payment = $this->entityManager->getRepository(ParcelPaymentInterface::class)->find($paymentId);

        Assert::isInstanceOf($payment, ParcelPaymentInterface::class);
        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);
        Assert::isInstanceOf($parcel, ParcelInterface::class);

        if (!$parcel->hasPayment($payment)) {
            throw new PaymentNotFoundException();
        }
        Assert::eq($payment->getState(), ParcelPayment::STATE_NEW);
        $payment->setMethod($paymentMethod);
        $this->entityManager->flush();

        return new JsonResponse($this->normalizer->normalize($parcel, null, ['groups' => 'shop:parcel:read']), 200);
    }
}
