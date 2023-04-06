<?php

namespace App\Controller;

use App\DTO\LowestPriceEnquiry;
use App\Filter\PromotionsFilterInterface;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Service\Serializer\DTOSerializer;
use Doctrine\Common\Proxy\Proxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private PromotionRepository $promotionRepository
    )
    {
    }

    #[Route('/products/{id}/lowest-price', name:'lowest-price', methods:['POST'])]
    public function lowestPrice(
        Request $request, 
        int $id, 
        DTOSerializer $serializer,
        PromotionsFilterInterface $promotionsFilter
    ): Response
    {
        if ($request->headers->has('force_fail')) {
            return new JsonResponse([
                ['error' => 'Promotions Engine failure message'],
                $request->headers->get('force_fail')
            ]);
        }

        /** @var LowestPriceEnquiry $lowestPriceEnquiry */
        $lowestPriceEnquiry = $serializer->deserialize($request->getContent(), LowestPriceEnquiry::class, 'json');

        $product = $this->productRepository->find($id);

        $lowestPriceEnquiry->setProduct($product);

        $promotions = $this->promotionRepository->findValidForProduct(
            $product, 
            date_create_immutable($lowestPriceEnquiry->getRequestDate())
        );
        
        $modifiedEnquiry = $promotionsFilter->apply($lowestPriceEnquiry, $promotions);

        $responseContent = $serializer->serialize($modifiedEnquiry, 'json');

        return new Response($responseContent, 200);
    }
}
