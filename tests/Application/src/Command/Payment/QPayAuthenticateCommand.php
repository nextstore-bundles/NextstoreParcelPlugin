<?php

declare(strict_types=1);

namespace Tests\Nextstore\SyliusParcelPlugin\Application\src\Command\Payment;

use Tests\Nextstore\SyliusParcelPlugin\Application\src\Factory\Payment\QPayFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QPayAuthenticateCommand extends Command
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var QPayFactory */
    private $qpayFactory;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        EntityManagerInterface $entityManager,
        QPayFactory $qpayFactory,
    ) {
        parent::__construct();

        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->entityManager = $entityManager;
        $this->qpayFactory = $qpayFactory;
    }

    protected function configure()
    {
        $this
            ->setName('payment:qpay:update-token')
            ->setDescription('Update the token to authenticate from QPay');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('*** Start: Proccess to update qpay token ***');

        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => 'qpay']);

        if (!$paymentMethod) {
            $output->writeln('Not found payment method');

            return 0;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (!$gatewayConfig) {
            $output->writeln('Not found gateway config');

            return 0;
        }

        $configs = $gatewayConfig->getConfig();

        if (!$configs) {
            $output->writeln('Not found configs');

            return 0;
        }

        $res = $this->qpayFactory->requestToken($configs);

        $accessToken = $res['access_token'];
        $expireAt = new \DateTime(date('Y-m-d H:i:s', $res['expires_in']));
        $replacedConfigs = array_replace($configs, ['token' => $accessToken, 'tokenExpireAt' => $expireAt]);
        $gatewayConfig->setConfig($replacedConfigs);
        $this->entityManager->flush();

        $output->writeln('Last token : ' . $accessToken);
        $output->writeln('*** End: Proccess to update qpay token ***');

        return 0;
    }
}
