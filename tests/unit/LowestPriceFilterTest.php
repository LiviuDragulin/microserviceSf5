<?php

namespace App\Tests\unit;

use App\DTO\LowestPriceEnquiry;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Filter\LowestPriceFilter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LowestPriceFilterTest extends WebTestCase
{
    /** @test */
    public function lowest_price_promotions_filtering_is_applied_correctly(): void
    {
        //Given
        $product = new Product();
        $product->setPrice(100);

        $lowestPriceFilter = static::getContainer()->get(LowestPriceFilter::class);

        $enquiry = new LowestPriceEnquiry();
        $enquiry->setProduct($product);
        $enquiry->setQuantity(5);

        $promotions = $this->promotionsDataProvider();

        //When
        $enquiryFiltered = $lowestPriceFilter->apply($enquiry, ...$promotions);

        //Then
        $this->assertSame(100, $enquiryFiltered->getPrice());
        $this->assertSame(50, $enquiryFiltered->getDiscountedPrice());
        $this->assertSame('Christmas sales mega discounts', $enquiryFiltered->getPromotionName());
    }

    public function promotionsDataProvider(): array
    {
        $promotion1 = new Promotion();
        $promotion1->setName('Christmas sales mega discounts');
        $promotion1->setAdjustment(0.5);
        $promotion1->setCriteria(["from" => "2023-12-15", "to" => "2024-01-5"]);
        $promotion1->setType('date_range_multiplier');

        $promotion2 = new Promotion();
        $promotion2->setName('Voucher 2w3e4r');
        $promotion2->setAdjustment(100);
        $promotion2->setCriteria(["code" => "2w3e4r"]);
        $promotion2->setType('fixed_price_voucher');

        $promotion3 = new Promotion();
        $promotion3->setName('Buy one get one free');
        $promotion3->setAdjustment(0.5);
        $promotion3->setCriteria(["minimum_quantity" => 2]);
        $promotion3->setType('even_items_multiplier');

        return [$promotion1, $promotion2, $promotion3];
    }
}
