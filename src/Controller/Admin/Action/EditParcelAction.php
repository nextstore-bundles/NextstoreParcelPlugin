<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Nextstore\SyliusParcelPlugin\Service\ParcelService;
use Doctrine\ORM\EntityManagerInterface;
use Nextstore\SyliusParcelPlugin\Model\ParcelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class EditParcelAction extends AbstractController
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
            $parcelId = $request->get('id');
            $parcel = $this->entityManager->getRepository(ParcelInterface::class)->find($parcelId);
            Assert::isInstanceOf($parcel, Parcel::class);
            if ($request->request->all()) {
                $params = $request->request->all();
            } else {
                $params = json_decode($request->getContent(), true);
            }
            $this->parcelService->editParcel($parcel, $params);
            $this->addFlash('success', 'Амжилттай засаж дууслаа');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse($referer);
        }

        return new RedirectResponse($referer);
    }
}
