<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Controller\Action\Payment\QPay;

use Tests\Nextstore\SyliusParcelPlugin\Application\src\Provider\QPayProvider;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

final class StatusAction implements ActionInterface, ApiAwareInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $response = $this->api->checkInvoice($payment);

        $rows = $response['rows'] ?? null;

        if ($response['count'] > 0) {
            foreach ($rows as $row) {
                if ($row['payment_status'] == 'PAID') {
                    $request->markCaptured();

                    return;
                }
            }
        } else {
            $request->markNew();

            return;
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof PaymentInterface;
    }

    public function setApi($qpayProvider): void
    {
        if (!$qpayProvider instanceof QPayProvider) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . QPayProvider::class);
        }

        $this->api = $qpayProvider;
    }
}
