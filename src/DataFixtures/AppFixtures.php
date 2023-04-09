<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductPromotion;
use App\Entity\Promotion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Christmas promotion fixture
        $product1 = new Product();
        $product1->setPrice(100);

        $promotion1 = new Promotion();
        $promotion1->setName('Christmas sales');
        $promotion1->setType('date_range_multiplier');
        $promotion1->setAdjustment(0.5);
        $promotion1->setCriteria(
            [
                'to' => '2024-01-05',
                'from' => '2023-12-15',
            ]
        );

        $productPromo1 = new ProductPromotion();
        $productPromo1->setProduct($product1);
        $productPromo1->setPromotion($promotion1);
        $productPromo1->setValidTo(new \DateTime('2024-01-05'));

        $manager->persist($product1);
        $manager->persist($promotion1);
        $manager->persist($productPromo1);

        //Fixed Voucher promotion fixture
        $product2 = new Product();
        $product2->setPrice(200);

        $promotion2 = new Promotion();
        $promotion2->setName('Voucher 2w3e4r');
        $promotion2->setType('fixed_price_voucher');
        $promotion2->setAdjustment(100);
        $promotion2->setCriteria(
            [
                'code' => '2w3e4r',
            ]
        );

        $productPromo2 = new ProductPromotion();
        $productPromo2->setProduct($product2);
        $productPromo2->setPromotion($promotion2);
        $productPromo2->setValidTo(NULL);

        $manager->persist($product2);
        $manager->persist($promotion2);
        $manager->persist($productPromo2);

        //Even items promotion fixture
        $product3 = new Product();
        $product3->setPrice(200);

        $promotion3 = new Promotion();
        $promotion3->setName('Buy one get one free');
        $promotion3->setType('even_items_multiplier');
        $promotion3->setAdjustment(0.5);
        $promotion3->setCriteria(
            [
                'minimum_quantity' => 2,
            ]
        );

        $productPromo3 = new ProductPromotion();
        $productPromo3->setProduct($product3);
        $productPromo3->setPromotion($promotion3);
        $productPromo3->setValidTo(NULL);

        $manager->persist($product3);
        $manager->persist($promotion3);
        $manager->persist($productPromo3);

        $manager->flush();
    }
}
