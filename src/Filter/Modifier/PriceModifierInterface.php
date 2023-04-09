<?php

namespace App\Filter\Modifier;

use App\DTO\PriceEnquiryInterface;
use App\Entity\Promotion;

interface PriceModifierInterface
{
    public function modify(int $price, int $quantity, Promotion $promotion, PriceEnquiryInterface $enquiry): int;
}
