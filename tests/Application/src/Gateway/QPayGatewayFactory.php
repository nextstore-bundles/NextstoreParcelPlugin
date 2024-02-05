<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Gateway;

use Tests\Nextstore\SyliusParcelPlugin\Application\src\Controller\Action\Payment\QPay\CaptureAction;
use Tests\Nextstore\SyliusParcelPlugin\Application\src\Controller\Action\Payment\QPay\StatusAction;
use Tests\Nextstore\SyliusParcelPlugin\Application\src\Provider\QPayProvider;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class QPayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'qpay_payment',
            'payum.factory_title' => 'QPay payment',
            'payum.action.status' => new StatusAction(),
            'payum.action.capture' => new CaptureAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new QPayProvider($config);
        };
    }
}
