<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Model\ParcelItem;
use Nextstore\SyliusParcelPlugin\Service\ParcelService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class EditParcelItemAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParcelService $parcelService,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');

        try {
            $parcelId = $request->get('parcelId');
            $parcel = $this->entityManager->getRepository(Parcel::class)->find($parcelId);
            Assert::isInstanceOf($parcel, Parcel::class);
            $itemId = $request->get('itemId');
            $item = $this->entityManager->getRepository(ParcelItem::class)->find($itemId);
            Assert::isInstanceOf($item, ParcelItem::class);

            if ($request->request->all()) {
                $params = $request->request->all();
            } else {
                $params = json_decode($request->getContent(), true);
            }

            $this->parcelService->editParcelItem($item, $params);
            $this->addFlash('success', 'Амжилттай засаж дууслаа');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse($referer);
        }

        return new RedirectResponse($referer);
    }
}
