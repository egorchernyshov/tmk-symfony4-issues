<?php

namespace App\Tests\Unit\Denormalizer;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Serializer\Json\ProductDenormalizer;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class ProductDenormalizerTest extends TestCase
{
    public function test()
    {
        $catEId = Factory::create()->numberBetween();
        $data = [
            'title' => $pTitle = Factory::create()->word,
            'eId' => $pEId = Factory::create()->numberBetween(),
            'price' => $pPrice = Factory::create()->randomFloat(2),
            'categoryEId' => [$catEId]
        ];

        $category = new Category();
        $category->setEId(Factory::create()->numberBetween());
        $category->setTitle(Factory::create()->word);

        $productSerializer = $this->getProductSerializerMock($category, $catEId);
        $product = $productSerializer->denormalize($data, Product::class, 'json', []);

        self::assertEquals($pEId, $product->getEId());
        self::assertEquals($pTitle, $product->getTitle());
        self::assertEquals($pPrice, $product->getPrice());
        self::assertEquals($category, current($product->getCategories()->toArray()));
    }

    private function getProductSerializerMock(Category $category, int $catEId): ProductDenormalizer
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(['eId' => $catEId])
            )
            ->willReturn($category);

        return new ProductDenormalizer($categoryRepository);
    }
}
