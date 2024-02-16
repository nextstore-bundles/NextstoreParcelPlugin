<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Service;

use Nextstore\SyliusParcelPlugin\Model\OrderItemStates;
use Nextstore\SyliusParcelPlugin\Exception\File\ErrorWhileReadingFileException;
use Nextstore\SyliusParcelPlugin\Validator\ValidatorFile;
use Nextstore\SyliusParcelPlugin\Validator\ValidatorOrderItem;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Resource\Factory\FactoryInterface as SyliusFactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderItemService
{
    /**
     * @param SyliusFactoryInterface<object> $productFactory
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorOrderItem $validatorOrderItem,
        private ValidatorFile $validatorFile,
        private ParameterBagInterface $parameterBag,
        private FactoryInterface $stateMachineFactory,
        private AwsFileUploader $awsFileUploader,
    ) {
    }

    /**
     * @param array<int,mixed> $array
     * @param mixed $itemId
     */
    public function editOrderItem(array $array, $itemId): void
    {
        /** @var OrderItemInterface $item */
        $item = $this->validatorOrderItem->validateEditOrderItem($itemId);

        $color = !empty($array['color']) ? $array['color'] : null;
        $description = !empty($array['description']) ? $array['description'] : null;
        $size = !empty($array['size']) ? $array['size'] : null;
        $price = !empty($array['price']) ? $array['price'] : null;
        $quantity = !empty($array['quantity']) ? $array['quantity'] : null;
        $trackingCode = !empty($array['trackingCode']) ? $array['trackingCode'] : null;

        $color && $item->setColor($color);
        $description && $item->setDescription($description);
        $size && $item->setSize($size);
        // $price && $item->setUnitPrice((int)((float) $price * 1000));
        $trackingCode && $item->setTrackingCode($trackingCode);

        if ($quantity == 0) {
            $quantity = 1;
        }

        if ($quantity > $item->getQuantity()) {
            while ($quantity - $item->getQuantity() > 0) {
                $unit = new OrderItemUnit($item);
                /** @var OrderInterface $order */
                $order = $item->getOrder();
                $unit->setShipment($order->getShipments()[0]);
                $item->addUnit($unit);

                $this->entityManager->persist($unit);
            }
        } elseif ($quantity < $item->getQuantity()) {
            $units = $item->getUnits();
            $i = 0;
            while ($item->getQuantity() - $quantity > 0) {
                $item->removeUnit($units[$i]);
                ++$i;
            }
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    /**
     * @throws ErrorWhileReadingFileException
     */
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
            $trackingCodeRow = '';
            foreach ($firstRow as $key => $item) {
                $item === 'Tracking code' && $trackingCodeRow = $key;
                $item === 'State' && $stateRow = $key;
            }

            $spreadsheet->getActiveSheet()->removeRow(1);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            foreach ($sheetData as $row) {
                $code = $row[$trackingCodeRow];
                $itemTransition = $row[$stateRow];

                if (!$itemTransition || !$code) {
                    continue;
                }

                $this->validatorOrderItem->validateInputTransition($itemTransition);
                $items = $this->entityManager->getRepository(OrderItemInterface::class)->findBy(['trackingCode' => $code]);
                /** @var OrderItem $item */
                foreach ($items as $item) {
                    $this->updateState($item, $itemTransition);
                }
            }
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw new ErrorWhileReadingFileException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param mixed $transition
     */
    public function updateState(OrderItemInterface $orderItem, $transition): void
    {
        $orderItemSM = $this->stateMachineFactory->get($orderItem, OrderItemStates::GRAPH_ORDER_ITEM);
        if ($orderItemSM->can($transition)) {
            $orderItemSM->apply($transition);
        }
    }

    /**
     * @param array<int,mixed> $items
     */
    public function downloadExcel(array $items): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($items) {
            $spreadsheet = new Spreadsheet();

            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Item Id');
            $sheet->setCellValue('B1', 'Order number');
            $sheet->setCellValue('C1', 'Customer email');
            $sheet->setCellValue('D1', 'Customer phone');
            $sheet->setCellValue('E1', 'Tracking code');
            $sheet->setCellValue('F1', 'State');
            $sheet->setCellValue('G1', 'Unit price');
            $sheet->setCellValue('H1', 'Quantity');
            $sheet->setCellValue('I1', 'Total price');
            $sheet->setCellValue('J1', 'Product code');
            $sheet->setCellValue('K1', 'Date');

            $date = new \DateTime();
            $dateString = $date->format('y-M-d_HA');
            $filename = 'ORDER-ITEMS-' . $dateString . '.xlsx';

            $writer = new Xlsx($spreadsheet);

            $count = 0;
            $row = 2;
            /** @var OrderItem $item */
            foreach ($items as $item) {
                if (0 === ++$count % 100) {
                    flush();
                }
                /** @var Order $order */
                $order = $item->getOrder();
                $sheet->setCellValue('A' . $row, $item->getId());
                $sheet->setCellValue('B' . $row, $order->getNumber() ?? 'Order number not set');
                $sheet->setCellValue('C' . $row, $order->getCustomer() ? $order->getCustomer()->getEmail() : 'No customer');
                $sheet->setCellValue('D' . $row, $order->getCustomer() ? $order->getCustomer()->getPhoneNumber() : 'No customer');
                $sheet->setCellValue('E' . $row, $item->getTrackingCode() ?? 'No tracking code set');
                $sheet->setCellValue('F' . $row, $item->getState() ?? 'No state set');
                $sheet->setCellValue('G' . $row, $item->getUnitPrice() / 100);
                $sheet->setCellValue('H' . $row, $item->getQuantity());
                $sheet->setCellValue('I' . $row, $item->getTotal() / 100);
                $sheet->setCellValue('J' . $row, $item->getProduct()->getCode());
                $sheet->setCellValue('K' . $row, $order->getCheckoutCompletedAt());

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
