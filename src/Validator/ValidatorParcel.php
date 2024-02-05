<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Validator;

use Nextstore\SyliusParcelPlugin\Model\Parcel;
use Webmozart\Assert\Assert;

class ValidatorParcel
{
    public function validateInputTransition(string $transition): void
    {
        $validTransitions = [
            Parcel::TRANSITION_CONFIRM,
            Parcel::TRANSITION_SHIP_TO_HOMELAND,
            Parcel::TRANSITION_ARRIVED_IN_HOMELAND,
            Parcel::TRANSITION_SHIP_TO_HOMELAND,
            Parcel::TRANSITION_SHIP_TO_CUSTOMER,
            Parcel::TRANSITION_DELIVER,
        ];

        Assert::inArray($transition, $validTransitions);
    }
}
