<?ihp

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Model;

use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class ParcelItem implements ParcelItemInterface
{
    use TimestampableTrait;
    use MeasurementTrait;

    protected ?int $id = null;

    protected ?int $total = 0;

    protected ?string $trackingCode;

    /** @var ParcelInterface|null */
    protected ?ParcelInterface $parcel;

    /** @var OrderItemInterface|null */
    protected ?OrderItemInterface $orderItem;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParcel(): ?ParcelInterface
    {
        return $this->parcel;
    }

    public function setParcel(?ParcelInterface $parcel): void
    {
        $this->parcel = $parcel;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getTrackingCode(): ?string
    {
        return $this->trackingCode;
    }

    public function setTrackingCode(?string $trackingCode): void
    {
        $this->trackingCode = $trackingCode;
    }

    public function getOrderItem(): OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(OrderItemInterface $orderItem): void
    {
        $this->orderItem = $orderItem;
    }
}
