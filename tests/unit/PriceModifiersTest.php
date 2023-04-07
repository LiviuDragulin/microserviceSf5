<?php

namespace App\Tests\unit;

use App\DTO\LowestPriceEnquiry;
use App\Entity\Promotion;
use App\Filter\Modifier\DateRangeMultiplier;
use App\Filter\Modifier\FixedPriceVoucher;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PriceModifiersTest extends WebTestCase
{
    /** @test */
    public function DateRangeMultiplier_returns_a_correctly_modified_price(): void
    {
        //Given
        $enquiry = new LowestPriceEnquiry();
        $enquiry->setQuantity(5);
        $enquiry->setRequestDate('2023-12-15');

        $promotion = new Promotion();
        $promotion->setName('Christmas sales mega discounts');
        $promotion->setAdjustment(0.5);
        $promotion->setCriteria(["from" => "2023-12-15", "to" => "2024-01-5"]);
        $promotion->setType('date_range_multiplier');

        $dateRangeMultiplier = new DateRangeMultiplier();

        //When
        $modifiedPrice = $dateRangeMultiplier->modify(100, 5, $promotion, $enquiry);

        //Then
        $this->assertEquals(250, $modifiedPrice);
    }

    /** @test */
    public function FixedPriceVoucher_returns_a_correctly_modified_price(): void
    {
        //Given
        $enquiry = new LowestPriceEnquiry();
        $enquiry->setQuantity(5);
        $enquiry->setVoucherCode('2w3e4r');

        $promotion = new Promotion();
        $promotion->setName('Voucher 2w3e4r');
        $promotion->setAdjustment(100);
        $promotion->setCriteria(["code" => "2w3e4r"]);
        $promotion->setType('fixed_price_voucher');

        $fixedPriceVoucher = new FixedPriceVoucher();

        //When
        $modifiedPrice = $fixedPriceVoucher->modify(150, 5, $promotion, $enquiry);

        //Then
        $this->assertEquals(500, $modifiedPrice);
    }
}
