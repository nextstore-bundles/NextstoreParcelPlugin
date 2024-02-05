<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Provider;

use Tests\Nextstore\SyliusParcelPlugin\Application\src\Factory\Payment\QPayFactory;
use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Payment\Model\PaymentInterface;

class QPayProvider
{
    /** @var ArrayObject */
    private $config;

    /** @var QPayFactory */
    private $qpayFactory;

    public function __construct(
        ArrayObject $config,
    ) {
        $this->config = $config;
        $this->qpayFactory = new QPayFactory();
    }

    public function generatePaymentForm(PaymentInterface $payment)
    {
        $clientId = $this->config['qpay_client_id'];
        $clientSecret = $this->config['qpay_client_secret'];
        $endPoint = $this->config['endpoint'];
        $templateId = $this->config['qpay_template_id'];
        $merchantId = $this->config['qpay_merchant_id'];
        $invoiceDescription = $this->config['qpay_invoice_description'];
        $accessToken = $this->config['token'];
        /** @var \DateTimeInterface $tokenExpireAt */
        $tokenExpireAt = $this->config['tokenExpireAt'];
        /** @var Parcel $parcel */
        $parcel = $payment->getParcel();
        $customer = $parcel->getCustomer();

        $now = new \DateTime();
        if ($tokenExpireAt < $now->getTimestamp()) {
            $accessToken = $this->getAccessToken($clientId, $clientSecret, $endPoint);
        }

        $client = $this->qpayFactory->prepareClient([
            'token' => $accessToken,
            'endPoint' => $endPoint,
        ]);

        $callbackUrl = $parcel->getChannel()->getHostname() . '/api/v2/shop/parcel/payment/qpay_callback' . '?payment_id=' . $parcel->getId();

        $email = filter_var($customer->getEmail(), \FILTER_VALIDATE_EMAIL) ? $customer->getEmail() : 'example@example.com';

        $billInfo = [
            'customer' => [
                'register_no' => 'none',
                'name' => $customer->getFirstName(),
                'email' => $email,
                'phone_number' => (string) $customer->getPhoneNumber(),
                'note' => 'none',
            ],
            'invoice' => [
                'id' => (string) $parcel->getId(),
                'description' => $invoiceDescription,
                'amount' => (int) $parcel->getTotal() / 100,
                'createDate' => (string) $parcel->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
            'invoice_code' => $templateId,
            'callback_url' => $callbackUrl,
        ];

        $qpayInvoiceResponse = $this->qpayFactory->createInvoice($billInfo, $client);

        return $qpayInvoiceResponse;
    }

    public function checkInvoice(PaymentInterface $payment)
    {
        $clientId = $this->config['qpay_client_id'];
        $clientSecret = $this->config['qpay_client_secret'];
        $endPoint = $this->config['endpoint'];
        $templateId = $this->config['qpay_template_id'];
        $merchantId = $this->config['qpay_merchant_id'];
        $accessToken = $this->config['token'];
        /** @var \DateTimeInterface $tokenExpireAt */
        $tokenExpireAt = $this->config['tokenExpireAt'];

        $parcel = $payment->getParcel();

        $details = $payment->getDetails();

        $qpayInvoiceNumber = $details['qpay']['invoice_id'];

        $now = new \DateTime();
        if ($tokenExpireAt < $now->getTimestamp()) {
            $accessToken = $this->getAccessToken($clientId, $clientSecret, $endPoint);
        }

        $client = $this->qpayFactory->prepareClient([
            'token' => $accessToken,
            'endPoint' => $endPoint,
        ]);

        $checkResponse = $this->qpayFactory->paymentCheck($qpayInvoiceNumber, $client);

        return $checkResponse;
    }

    public function getAccessToken($clientId, $clientSecret, $endPoint)
    {
        $res = $this->qpayFactory->requestToken([
            'endpoint' => $endPoint,
            'qpay_client_id' => $clientId,
            'qpay_client_secret' => $clientSecret,
        ]);

        $accessToken = $res['access_token'];

        return $accessToken;
    }
}
