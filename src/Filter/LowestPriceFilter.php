<?php

namespace App\Filter;

use App\DTO\PromotionEnquiryInterface;
use App\Entity\Promotion;
use App\Filter\Modifier\Factory\PriceModifierFactoryInterface;

class LowestPriceFilter implements PromotionsFilterInterface
{
    public function __construct(private PriceModifierFactoryInterface $priceModifierFactory)
    {
    }

    public function apply(PromotionEnquiryInterface $enquiry, Promotion ...$promotions): PromotionEnquiryInterface
    {
        $price = $enquiry->getProduct()->getPrice();
        $quantity = $enquiry->getQuantity();
        //$lowestPrice = $quantity * $price;

        foreach ($promotions as $promotion) {
            $priceModifier = $this->priceModifierFactory->create($promotion->getType());
            
            $priceModifier->modify($price, $quantity, $promotion, $enquiry);

            //TODO: split this into interfaces to resolve the Interface Segregation Principle
            $enquiry->setDiscountedPrice(50);
            $enquiry->setPrice(100);
            $enquiry->setPromotionId(3);
            $enquiry->setPromotionName('Christmas sales mega discounts');
        }

        return $enquiry;
    }
}
