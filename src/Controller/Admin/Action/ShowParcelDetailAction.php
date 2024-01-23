<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Controller\Admin\Action;

use Nextstore\SyliusParcelPlugin\Doctrine\ORM\Repository\OrderItemRepository;
use Nextstore\SyliusParcelPlugin\Entity\Parcel\Parcel;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Customer\Model\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Webmozart\Assert\Assert;

class ShowParcelDetailAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Environment $twig,
        private OrderItemRepository $orderItemRepository,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function __invoke(Request $request): Response
    {
        $parcelId = $request->get('id', null);

        $parcel = $this->entityManager->getRepository(Parcel::class)->find($parcelId);
        Assert::isInstanceOf($parcel, Parcel::class);

        $customer = $parcel->getCustomer();
        Assert::isInstanceOf($customer, Customer::class);

        $notInParcelOrderItems = $this->orderItemRepository->getNotPackedItemsForParcel($customer);

        return new Response(
            $this->twig->render('@NextstoreSyliusParcelPlugin/Admin/Parcel/show.html.twig', [
                'otherItems' => $notInParcelOrderItems,
                'parcel' => $parcel,
            ]),
        );
    }
}
