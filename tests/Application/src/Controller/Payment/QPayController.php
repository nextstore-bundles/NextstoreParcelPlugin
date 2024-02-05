<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Controller\Payment;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelPayment;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

final class QPayController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ViewHandlerInterface */
    private $viewHandler;

    /** @var Payum */
    private $payum;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PaymentRepositoryInterface */
    private $paymentRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ViewHandlerInterface $viewHandler,
        Payum $payum,
        EntityManagerInterface $entityManager,
        PaymentRepositoryInterface $paymentRepository,
        LoggerInterface $logger,
        private Security $security,
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->viewHandler = $viewHandler;
        $this->payum = $payum;
        $this->entityManager = $entityManager;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
    }

    public function detailAction(Request $request)
    {
        $userToken = $this->tokenStorage->getToken();
        $user = $userToken->getUser();
        if (null === $userToken || !is_object($user)) {
            throw new AccessDeniedHttpException();
        }

        $parcelId = $request->attributes->get('parcelId');
        $parcel = $this->entityManager->getRepository(Parcel::class)->findOneBy(['id' => $parcelId]);
        $payment = $parcel->getLastPayment(PaymentInterface::STATE_NEW);

        if (null === $payment) {
            throw new NotFoundHttpException('Payment could not be found');
        }

        $paymentDetails = $payment->getDetails();

        if (count($paymentDetails) > 0 && array_key_exists('invoice_id', $paymentDetails['qpay'])) {
            return $this->viewHandler->handle(
                View::create(
                    $paymentDetails,
                    Response::HTTP_OK,
                ),
            );
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        // $storage = $this->payum->getStorage($payment);
        $this->payum->getGateway($gatewayConfig->getGatewayName())->execute($capture = new Capture($payment));
        $payment = $capture->getModel();
        $result = $payment->getDetails();

        return $this->viewHandler->handle(
            View::create(
                $result,
                Response::HTTP_OK,
            ),
        );
    }

    public function checkAction(Request $request)
    {
        $userToken = $this->tokenStorage->getToken();
        $user = $userToken->getUser();
        if (null === $userToken || !is_object($user)) {
            throw new AccessDeniedHttpException();
        }

        $paymentId = $request->attributes->get('id');
        $payment = $this->entityManager->getRepository(ParcelPayment::class)->find((int) $paymentId);

        if (null === $payment) {
            throw new NotFoundHttpException('Payment could not be found');
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        $this->payum->getGateway($gatewayConfig->getGatewayName())->execute($humanStatus = new GetHumanStatus($payment));

        $paymentStatus = $humanStatus->getValue();

        return $this->viewHandler->handle(
            View::create(
                [
                    'status' => $paymentStatus,
                    'paymentState' => $payment->getState(),
                ],
                Response::HTTP_OK,
            ),
        );
    }

    public function acceptQPayPaymentAction(Request $request)
    {
        $parcelId = $request->query->get('payment_id');
        $this->logger->error('::Qpay :: invoiceId: ' . $parcelId);
        $parcel = $this->entityManager->getRepository(Parcel::class)->find((int) $parcelId);

        if (null === $parcel) {
            throw new NotFoundHttpException('Order could not be found');
        }

        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW);

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        $this->payum->getGateway($gatewayConfig->getGatewayName())->execute($humanStatus = new GetHumanStatus($payment));

        $paymentStatus = $humanStatus->getValue();

        return $this->viewHandler->handle(
            View::create(
                [
                    'status' => $paymentStatus,
                    'paymentState' => $payment->getState(),
                ],
                Response::HTTP_OK,
            ),
        );
    }
}
