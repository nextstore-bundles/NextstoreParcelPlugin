<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Doctrine\ORM\Repository\OrderItemRepository;
use Nextstore\SyliusParcelPlugin\Form\Type\OrderItemFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ShowOrderItemsListAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Environment $twig,
        private OrderItemRepository $orderItemRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $phone = $request->get('phone');
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
            'state' => null,
            'orderNumber' => $orderNumber,
            'trackingCode' => $trackingCode,
        ]);

        $items = $this->orderItemRepository->getPackableItems($phone, $startDate, $endDate, $orderBy, $orderNumber, $trackingCode);

        $groupedResults = array_reduce($items, function ($accumulator, $orderItem) {
            $customer = !empty($orderItem['phone']) ? $orderItem['phone'] : $orderItem['email'];

            if (!isset($accumulator[$customer])) {
                $accumulator[$customer] = [];
            }
            $accumulator[$customer][] = $orderItem;

            return $accumulator;
        }, []);

        $adapter = new ArrayAdapter($groupedResults);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(5);
        $pager->setCurrentPage((int) $request->query->get('page', 1));

        return new Response($this->twig->render('@NextstoreSyliusParcelPlugin/Admin/Parcel/order_items_index.html.twig', [
            'items' => $pager,
            'form' => $filterForm->createView(),
        ]));
    }
}
