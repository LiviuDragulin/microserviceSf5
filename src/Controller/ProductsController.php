<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class ProductsController
{
    #[Route('/products/{id}/lowest-price', name:'lowest-price', methods:['POST'])]
    public function lowesPrice(int $id)
    {
        dd($id);
    }
}
