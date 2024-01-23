<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Service\OrderItemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class EditOrderItemAction extends AbstractController
{
    private OrderItemService $orderItemService;

    public function __construct(OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');

        try {
            $data = $request->request->all();
            $itemId = $request->get('id');
            $this->orderItemService->editOrderItem($data, $itemId);
            $this->addFlash('success', 'Амжилттай засаж дууслаа');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse($referer);
        }

        return new RedirectResponse($referer);
    }
}
