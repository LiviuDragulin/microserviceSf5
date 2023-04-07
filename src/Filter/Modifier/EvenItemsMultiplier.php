<?php

namespace App\Filter\Modifier;

use App\DTO\PromotionEnquiryInterface;
use App\Entity\Promotion;

class EvenItemsMultiplier implements PriceModifierInterface
{
    /**
     * Calculation is: ((evenCount * productPrice) * priceAdjustment) + (oddCount * productPrice)
     * The variables are:
     * oddCount = use modulus of 2 to see if we get: 0 or 1
     * evenCount (highest even number) = use the oddCount to see how many products of even groups we have
     */
    public function modify(
        int $price,
        int $quantity, 
        Promotion $promotion, 
        PromotionEnquiryInterface $enquiry
    ): int
    {
        if ($quantity < 2) {
            return $price * $quantity;
        }

        $oddCount = $quantity % 2;

        $evenCount = $quantity - $oddCount;

        return (($evenCount * $price) * $promotion->getAdjustment()) + ($oddCount * $price);
    }
}
