<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Service\ParcelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class PackParcelAction extends AbstractController
{
    private ParcelService $parcelService;

    public function __construct(ParcelService $parcelService)
    {
        $this->parcelService = $parcelService;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');
        if ($request->request->all()) {
            $params = $request->request->all();
        } else {
            $params = json_decode($request->getContent(), true);
        }

        try {
            Assert::keyExists($params, 'itemIds', 'Order item сонгоогүй байна');
            Assert::keyExists($params, 'customerId');
            $addressId = isset($params['addressId']) && !empty($params['addressId']) ? (int) $params['addressId'] : null;

            $parcel = $this->parcelService->packItems($params['itemIds'], (int) $params['customerId'], $addressId);
            $this->addFlash('success', 'Амжилттай багцаллаа');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse($referer);
        }

        return new RedirectResponse($referer);
    }
}
