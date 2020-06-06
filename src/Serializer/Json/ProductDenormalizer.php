<?php

namespace App\Serializer\Json;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductDenormalizer implements DenormalizerInterface
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $product = new Product();
        $product->setTitle($data['title']);
        $product->setPrice($data['price']);
        $product->setEId($data['eId']);

        foreach ($this->takeCategoriesIds($data) as $catEId) {
            $category = $this->categoryRepository->findOneBy(['eId' => $catEId]);

            if ($category) {
                $product->addCategory($category);
            }
        }

        return $product;
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function takeCategoriesIds($data): array
    {
        return $data['categoriesEId'] ?? $data['categoryEId'] ?? [];
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Product::class;
    }
}