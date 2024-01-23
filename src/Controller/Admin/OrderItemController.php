<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin;

use Nextstore\SyliusParcelPlugin\Doctrine\ORM\Repository\OrderItemRepository;
use Nextstore\SyliusParcelPlugin\Form\Type\OrderItemFilterType;
use Nextstore\SyliusParcelPlugin\Service\OrderItemService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class OrderItemController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Environment $twig,
        private OrderItemService $orderItemService,
        private OrderItemRepository $orderItemRepository,
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

        $filterForm = $this->createForm(OrderItemFilterType::class, [
            'phone' => $phone,
            'from' => $from,
            'to' => $to,
            'orderBy' => $orderBy,
            'state' => $state,
            'orderNumber' => $orderNumber,
            'trackingCode' => $trackingCode,
        ]);

        $items = $this->orderItemRepository->getItemsWithTrackingCode($state, $phone, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode);

        $adapter = new ArrayAdapter($items);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage((int) $request->get('page', 1));

        return new Response($this->twig->render(
            '@NextstoreSyliusParcelPlugin/Admin/Parcel/manage_order_items.html.twig',
            [
                'form' => $filterForm->createView(),
                'items' => $pager,
            ],
        ));
    }

    public function updateStateFromExcel(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');

        try {
            $file = $request->files->get('excel-file');
            $this->orderItemService->updateStatesFromExcel($file);
            $this->addFlash('success', 'Successfully updated order item state');
        } catch (\Exception $e) {
            $this->addFlash('success', 'Successfully updated order item state');
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

        $items = $this->orderItemRepository->getItemsWithTrackingCode($state, $phone, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode);

        $response = $this->orderItemService->downloadExcel($items);

        return $response;
    }
}
