<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Entity\Parcel\Parcel;
use Nextstore\SyliusParcelPlugin\Service\ParcelService;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class AddItemsToParcelAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParcelService $parcelService,
    ) {
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
            $parcelId = $request->get('id');
            $parcel = $this->entityManager->getRepository(Parcel::class)->find($parcelId);
            Assert::isInstanceOf($parcel, Parcel::class);
            Assert::eq($parcel->getState(), Parcel::STATE_NEW, 'Зөвхөн Шинэ төлөвтэй байх үед нэмэх боломжтой');

            Assert::keyExists($params, 'itemIds', 'Order item сонгоогүй байна');
            Assert::keyExists($params, 'customerId');

            $customer = $this->entityManager->getRepository(Customer::class)->find($params['customerId']);
            Assert::isInstanceOf($customer, Customer::class);

            if ($parcel->getCustomer()->getId() !== $customer->getId()) {
                throw new \Exception('Invalid item for parcel');
            }

            $this->parcelService->addItemsToParcel($parcel, $params['itemIds']);
            $this->addFlash('success', 'Амжилттай илгээмж рүү нэмлээ');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse($referer);
        }

        return new RedirectResponse($referer);
    }
}
