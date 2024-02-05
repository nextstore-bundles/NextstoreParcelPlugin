<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Controller\Action\Payment\QPay;

use Tests\Nextstore\SyliusParcelPlugin\Application\src\Provider\QPayProvider;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
// use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @param Convert|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $details = $payment->getDetails();

        if (!array_key_exists('qpay', $details) || !array_key_exists('invoice_id', $details['qpay'])) {
            $generatedForm = $this->api->generatePaymentForm($payment);
            $details['qpay'] = $generatedForm;
            $payment->setDetails($details);
            // $payment->setExternalInvoiceNumber($generatedForm['invoice_id']);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface;
    }

    public function setApi($qpayProvider): void
    {
        if (!$qpayProvider instanceof QPayProvider) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . QPayProvider::class);
        }

        $this->api = $qpayProvider;
    }
}
