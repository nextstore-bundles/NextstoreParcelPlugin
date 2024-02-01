<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Service;

use Nextstore\SyliusParcelPlugin\Model\OrderItemInterface;
use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelItem;
use Nextstore\SyliusParcelPlugin\Exception\File\ErrorWhileReadingFileException;
use Nextstore\SyliusParcelPlugin\Validator\ValidatorFile;
use Nextstore\SyliusParcelPlugin\Validator\ValidatorParcel;
use Doctrine\ORM\EntityManagerInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelPaymentInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SM\Factory\FactoryInterface as SMFactory;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Component\Core\Resolver\DefaultPaymentMethodResolver;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Order\Model\Order;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Webmozart\Assert\Assert;

class ParcelService
{
    /**
     * @param FactoryInterface<object> $parcelItemFactory
     * @param FactoryInterface<object> $parcelFactory
     * @param FactoryInterface<object> $paymentFactory
     */
    public function __construct(
        private EntityManagerInterface $em,
        private CurrencyContextInterface $currencyContext,
        private FactoryInterface $parcelFactory,
        private FactoryInterface $parcelItemFactory,
        private SMFactory $stateMachineFactory,
        private AwsFileUploader $awsFileUploader,
        private ParameterBagInterface $parameterBag,
        private ValidatorParcel $validatorParcel,
        private ValidatorFile $validatorFile,
        private DefaultPaymentMethodResolver $defaultPaymentMethodResolver,
        private ChannelContextInterface $channelContext,
        private FactoryInterface $parcelPaymentFactory
    ) {
    }

    /**
     * @param array<int,mixed> $itemIds
     */
    public function packItems(array $itemIds, int $customerId, ?int $addressId): Parcel
    {
        $channel = $this->channelContext->getChannel();
        /** @var Parcel $parcel */
        $parcel = $this->parcelFactory->createNew();
        $parcel->setState(Parcel::STATE_NEW);
        $parcel->setChannel($channel);

        $customer = $this->em->getRepository(Customer::class)->find($customerId);
        Assert::isInstanceOf($customer, Customer::class);
        $parcel->setCustomer($customer);
        $parcel->setCurrencyCode($this->currencyContext->getCurrencyCode());

        if (!$addressId) {
            $address = $customer->getAddresses()[0];
        } else {
            $address = $this->em->getRepository(Address::class)->find($addressId);
            if (!$address instanceof Address) {
                $address = $customer->getAddresses()[0];
            }
        }
        Assert::isInstanceOf($address, Address::class);
        $parcel->setAddress($address);
        foreach ($itemIds as $id) {
            $item = $this->em->getRepository(OrderItemInterface::class)->find($id);
            Assert::isInstanceOf($item, OrderItemInterface::class);
            $order = $item->getOrder();
            Assert::isInstanceOf($order, Order::class);

            /** @var ParcelItem $parcelItem */
            $parcelItem = $this->parcelItemFactory->createNew();
            $parcelItem->setOrderItem($item);
            $parcelItem->setParcel($parcel);
            $parcelItem->setTrackingCode($item->getTrackingCode());
            $parcel->addItem($parcelItem);
            $this->em->persist($parcelItem);
        }

        $this->em->persist($parcel);
        $this->em->flush();

        return $parcel;
    }

    public function createParcelPayment(Parcel $parcel): void
    {
        $channel = $this->channelContext->getChannel();
        if ($parcel->hasPayments()) {
            $parcel->recalculateTotal();

            return;
        }

        // $payment = new SyliusPaymentInterface();
        $payment = $this->parcelPaymentFactory->createNew();
        $methods = $this->em->getRepository(PaymentMethod::class)->findEnabledForChannel($channel);
        Assert::isInstanceOf($methods[0], PaymentMethod::class);

        $payment->setParcel($parcel);
        $payment->setCurrencyCode($parcel->getCurrencyCode());
        $payment->setAmount((int) $parcel->getTotal());
        $payment->setMethod($methods[0]);
        $payment->setState(ParcelPaymentInterface::STATE_NEW);
        $payment->setDetails([]);

        $this->em->persist($payment);
        $this->em->flush();
    }

    /**
     * @param array<int,mixed> $params
     */
    public function editParcel(Parcel $parcel, array $params): void
    {
        Assert::notNull($params['total']);
        Assert::notNull($params['length']);
        Assert::notNull($params['width']);
        Assert::notNull($params['height']);
        Assert::notNull($params['weight']);
        Assert::notNull($params['code']);
        Assert::notNull($params['notes']);

        !empty($params['code']) && $parcel->setCode($params['code']);
        !empty($params['width']) && $parcel->setWidth((float) $params['width']);
        !empty($params['length']) && $parcel->setLength((float) $params['length']);
        !empty($params['height']) && $parcel->setHeight((float) $params['height']);
        !empty($params['weight']) && $parcel->setWeight((float) $params['weight']);
        !empty($params['total']) && $parcel->setTotal((int) $params['total'] * 100);
        !empty($params['notes']) && $parcel->setNotes($params['notes']);
        $parcel->setUpdatedAt(new \DateTime());

        $this->em->flush();
    }

    /**
     * @param array<int,mixed> $params
     */
    public function editParcelItem(ParcelItem $item, array $params): void
    {
        Assert::notNull($params['total']);
        Assert::notNull($params['length']);
        Assert::notNull($params['width']);
        Assert::notNull($params['height']);
        Assert::notNull($params['weight']);
        Assert::notNull($params['trackingCode']);

        !empty($params['width']) && $item->setWidth((float) $params['width']);
        !empty($params['length']) && $item->setLength((float) $params['length']);
        !empty($params['height']) && $item->setHeight((float) $params['height']);
        !empty($params['weight']) && $item->setWeight((float) $params['weight']);
        !empty($params['total']) && $item->setTotal((int) $params['total'] * 100);
        !empty($params['trackingCode']) && $item->setTrackingCode($params['trackingCode']);

        $this->em->flush();
    }

    /**
     * @param mixed $itemIds
     */
    public function addItemsToParcel(Parcel $parcel, $itemIds): void
    {
        foreach ($itemIds as $itemId) {
            $orderItem = $this->em->getRepository(OrderItem::class)->find($itemId);
            $parcelItem = $this->parcelItemFactory->createNew();
            $parcelItem->setParcel($parcel);
            $parcelItem->setOrderItem($orderItem);
            $parcelItem->setTrackingCode($orderItem->getTrackingCode());
            $this->em->persist($parcelItem);
        }
        $parcel->setUpdatedAt(new \DateTime());

        $this->em->flush();
    }

    public function updateStatesFromExcel(UploadedFile $file): void
    {
        $this->validatorFile->validateExcel($file);

        try {
            $reader = IOFactory::createReader(IOFactory::READER_XLSX);
            $reader->setReadDataOnly(true);

            $fileFromAws = $this->awsFileUploader->upload($file);
            $spreadsheet = $reader->load($fileFromAws);
            $path = $this->parameterBag->get('kernel.project_dir') . '/public/' . $fileFromAws;
            unlink($path);

            $firstRow = $spreadsheet->getActiveSheet()->toArray(null, true, true, true)[1];
            $stateRow = '';
            $parcelCodeRow = '';
            foreach ($firstRow as $key => $item) {
                $item === 'Parcel code' && $parcelCodeRow = $key;
                $item === 'State' && $stateRow = $key;
            }

            $spreadsheet->getActiveSheet()->removeRow(1);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            foreach ($sheetData as $row) {
                $code = $row[$parcelCodeRow];
                $itemTransition = $row[$stateRow];

                if (!$itemTransition || !$code) {
                    continue;
                }

                $this->validatorParcel->validateInputTransition($itemTransition);
                $parcels = $this->em->getRepository(Parcel::class)->findBy(['code' => $code]);
                /** @var Parcel $parcel */
                foreach ($parcels as $parcel) {
                    $this->updateState($parcel, $itemTransition);
                }
            }
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new ErrorWhileReadingFileException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param mixed $transition
     */
    public function updateState(Parcel $parcel, $transition): void
    {
        $parcelSM = $this->stateMachineFactory->get($parcel, Parcel::GRAPH_PARCEL);
        if ($parcelSM->can($transition)) {
            $parcelSM->apply($transition);
        }
    }

    /**
     * @param array<int,mixed> $parcels
     */
    public function downloadExcel(array $parcels): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($parcels) {
            $spreadsheet = new Spreadsheet();

            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Parcel Id');
            $sheet->setCellValue('B1', 'Parcel code');
            $sheet->setCellValue('C1', 'State');
            $sheet->setCellValue('D1', 'Customer email');
            $sheet->setCellValue('E1', 'Customer phone');
            $sheet->setCellValue('F1', 'Width');
            $sheet->setCellValue('G1', 'Height');
            $sheet->setCellValue('H1', 'Length');
            $sheet->setCellValue('I1', 'Weight');
            $sheet->setCellValue('J1', 'Items');
            $sheet->setCellValue('K1', 'Total');
            $sheet->setCellValue('L1', 'Date');

            $date = new \DateTime();
            $dateString = $date->format('y-M-d_HA');
            $filename = 'PARCELS-' . $dateString . '.xlsx';

            $writer = new Xlsx($spreadsheet);

            $count = 0;
            $row = 2;
            /** @var Parcel $parcel */
            foreach ($parcels as $parcel) {
                if (0 === ++$count % 100) {
                    flush();
                }

                $sheet->setCellValue('A' . $row, $parcel->getId());
                $sheet->setCellValue('B' . $row, $parcel->getCode() ?? 'Parcel code not set');
                $sheet->setCellValue('C' . $row, $parcel->getState() ?? 'Parcel state not set');
                $sheet->setCellValue('D' . $row, $parcel->getCustomer() ? $parcel->getCustomer()->getEmail() : 'No customer');
                $sheet->setCellValue('E' . $row, $parcel->getCustomer() ? $parcel->getCustomer()->getPhoneNumber() : 'No customer');
                $sheet->setCellValue('F' . $row, $parcel->getWidth() ?? 'No width');
                $sheet->setCellValue('G' . $row, $parcel->getHeight() ?? 'No state set');
                $sheet->setCellValue('H' . $row, $parcel->getLength() ?? 'No length');
                $sheet->setCellValue('I' . $row, $parcel->getWeight() ?? 'No weight');
                $sheet->setCellValue('J' . $row, count($parcel->getItems()));
                $sheet->setCellValue('K' . $row, $parcel->getTotal() / 100);
                $sheet->setCellValue('L' . $row, $parcel->getCreatedAt());

                ++$row;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        });

        return $response;
    }
}
