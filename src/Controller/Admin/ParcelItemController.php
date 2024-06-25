<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin;

use Nextstore\SyliusParcelPlugin\Model\ParcelItem;
use Nextstore\SyliusParcelPlugin\Form\Type\ParcelItemFilterType;
use Nextstore\SyliusParcelPlugin\Repository\Parcel\ParcelItemRepository;
use Nextstore\SyliusParcelPlugin\Service\ParcelService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class ParcelItemController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParcelItemRepository $parcelItemRepository,
        private Environment $twig,
        private ParcelService $parcelService,
    ) {
    }

    public function getList(Request $request): Response
    {
        $phone = $request->get('phone');
        $state = $request->get('state') ?? null;
        $startDate = $request->get('startDate') ?? null;
        $endDate = $request->get('endDate') ?? null;
        $from = $startDate ? new \DateTime($startDate) : null;
        $to = $endDate ? new \DateTime($endDate) : null;
        $orderBy = $request->get('orderBy', 'ASC');
        $orderNumber = $request->get('orderNumber') ?? null;
        $trackingCode = $request->get('trackingCode') ?? null;
        $parcelCode = $request->get('parcelCode') ?? null;

        $filterForm = $this->createForm(ParcelItemFilterType::class, [
            'phone' => $phone,
            'from' => $from,
            'to' => $to,
            'orderBy' => $orderBy,
            'state' => $state,
            'orderNumber' => $orderNumber,
            'trackingCode' => $trackingCode,
            'parcelCode' => $parcelCode,
        ]);


        $parcels = $this->parcelItemRepository->getItems($state, $phone, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode, $parcelCode);

        $adapter = new ArrayAdapter($parcels);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage((int) $request->get('page', 1));

        return new Response($this->twig->render(
            '@NextstoreSyliusParcelPlugin/Admin/Parcel/manage_parcel_items.html.twig',
            [
                'form' => $filterForm->createView(),
                'parcels' => $pager,
            ],
        ));
    }

    public function updateStateFromExcel(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');

        try {
            $file = $request->files->get('excel-file');

            $this->parcelService->updateStatesFromExcel($file);
            $this->addFlash('success', 'Successfully updated parcel state');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return new RedirectResponse($referer);
        }
    }

    public function downloadExcel(Request $request): StreamedResponse
    {
        $referer = $request->headers->get('referer');

        $phone = $request->get('phone');
        $state = $request->get('state') ?? null;
        $startDate = $request->get('startDate') ?? null;
        $endDate = $request->get('endDate') ?? null;
        $from = $startDate ? new \DateTime($startDate) : null;
        $to = $endDate ? new \DateTime($endDate) : null;
        $orderBy = $request->get('orderBy', 'ASC');
        $orderNumber = $request->get('orderNumber') ?? null;
        $trackingCode = $request->get('trackingCode') ?? null;
        $parcelCode = $request->get('parcelCode') ?? null;

        /** @var ParcelItemRepository $parcelItemRepository */
        $parcelItemRepository = $this->entityManager->getRepository(ParcelItem::class);
        $parcels = $parcelItemRepository->getItems($state, $phone, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode, $parcelCode);

        $response = $this->parcelService->downloadExcel($parcels);

        return $response;
    }
}
