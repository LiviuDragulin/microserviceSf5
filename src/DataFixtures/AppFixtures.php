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
        $product1 = new Product();
        $product1->setPrice(100);

        $product2 = new Product();
        $product2->setPrice(200);

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

        $promotion2 = new Promotion();
        $promotion2->setName('Voucher 2w3e4r');
        $promotion2->setType('fixed_price_voucher');
        $promotion2->setAdjustment(100);
        $promotion2->setCriteria(
            [
                'code' => '2w3e4r',
            ]
        );

        $productPromo1 = new ProductPromotion();
        $productPromo1->setProduct($product1);
        $productPromo1->setPromotion($promotion1);
        $productPromo1->setValidTo(new \DateTime('2024-01-05'));

        $productPromo2 = new ProductPromotion();
        $productPromo2->setProduct($product2);
        $productPromo2->setPromotion($promotion2);
        $productPromo2->setValidTo(NULL);

        $manager->persist($product1);
        $manager->persist($product2);

        $manager->persist($promotion1);
        $manager->persist($promotion2);

        $manager->persist($productPromo1);
        $manager->persist($productPromo2);

        $manager->flush();
    }
}
