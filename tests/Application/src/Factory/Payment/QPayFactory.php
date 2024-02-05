<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Factory\Payment;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class QPayFactory
{
    public function requestToken($configs)
    {
        $client = new Client([
            'base_uri' => $configs['endpoint'],
            'auth' => [$configs['qpay_client_id'], $configs['qpay_client_secret']],
        ]);

        try {
            $res = $client->request('POST', 'auth/token');
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $decoded = json_decode((string) $res->getBody(), true, 512, \JSON_THROW_ON_ERROR);

        return $decoded;
    }

    public function prepareClient($configs): Client
    {
        $client = new Client([
            'base_uri' => $configs['endPoint'],
            'headers' => [
                'Authorization' => 'Bearer ' . $configs['token'],
                'Accept' => 'application/json',
            ],
        ]);

        return $client;
    }

    /**
     * @return mixed
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function paymentCheck(string $qpayInvoiceNumber, Client $client)
    {
        $data = [
            'object_type' => 'INVOICE',
            'object_id' => $qpayInvoiceNumber,
            'offset' => [
                'page_number' => 1,
                'page_limit' => 100,
            ],
        ];

        try {
            $qpayInvoiceResponse = $client->request('POST', 'payment/check', [
                'json' => $data,
            ]);

            return json_decode((string) $qpayInvoiceResponse->getBody(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (ClientException $e) {
            return json_decode((string) $e->getResponse()->getBody(true), true, 512, \JSON_THROW_ON_ERROR);
        }
    }

    public function createInvoice($billInfo, Client $client)
    {
        $data = [
            'invoice_code' => $billInfo['invoice_code'],
            'sender_invoice_no' => $billInfo['invoice']['id'],
            'sender_branch_code' => '1',
            'invoice_receiver_code' => 'easystore_terminal',
            'invoice_description' => $billInfo['invoice']['description'],
            'invoice_due_date' => $billInfo['invoice']['createDate'],
            'allow_partial' => false,
            'minimum_amount' => null,
            'allow_exceed' => false,
            'maximum_amount' => null,
            'amount' => $billInfo['invoice']['amount'],
            'callback_url' => $billInfo['callback_url'],
            'sender_staff_code' => 'online',
            'invoice_receiver_data' => [
                'register' => $billInfo['customer']['register_no'],
                'name' => $billInfo['customer']['name'],
                'email' => $billInfo['customer']['email'],
                'phone' => $billInfo['customer']['phone_number'],
            ],
        ];

        try {
            $qpayInvoiceResponse = $client->request('POST', 'invoice', [
                'json' => $data,
            ]);

            return json_decode((string) $qpayInvoiceResponse->getBody(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (ClientException $e) {
            return json_decode((string) $e->getResponse()->getBody(true), true, 512, \JSON_THROW_ON_ERROR);
        }
    }
}
