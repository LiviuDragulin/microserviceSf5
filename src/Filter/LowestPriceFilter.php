<?php

namespace App\Filter;

use App\DTO\PromotionEnquiryInterface;
use App\Entity\Promotion;

class LowestPriceFilter implements PromotionsFilterInterface
{
    public function apply(PromotionEnquiryInterface $enquiry, Promotion ...$promotion): PromotionEnquiryInterface
    {
        $price = $enquiry->getProduct()->getPrice();
        $quantity = $enquiry->getQuantity();
        //$lowestPrice = $quantity * $price;

        //$modifiedPrice = $priceModifier->modify($price, $quantity, $promotion, $enquiry);

        //TODO: split this into interfaces to resolve the Interface Segregation Principle
        $enquiry->setDiscountedPrice(50);
        $enquiry->setPrice(100);
        $enquiry->setPromotionId(3);
        $enquiry->setPromotionName('Christmas sales mega discounts');

        return $enquiry;
    }
}
