<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;

class ProductCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $count = 50;
        while ($count--) {
            $product = new Product();
            $product->setPrice(Factory::create()->randomFloat(2, 0, 200));
            $product->setEId(Factory::create()->numberBetween());
            $product->setTitle(ucfirst(Factory::create()->text(12)));
            $manager->persist($product);

            $category = new Category();
            $category->setEId(Factory::create()->numberBetween());
            $category->setTitle(ucfirst(Factory::create()->text(12)));
            $manager->persist($category);
        }

        $manager->flush();

        foreach ($this->getRandomProducts($manager) as $product) {
            foreach ($this->getRandomCategories($manager) as $category) {
                /** @var Product $product */
                $product->addCategory($category);
            }

            $manager->persist($product);
        }

        $manager->flush();

        foreach ($this->getRandomCategories($manager) as $category) {
            foreach ($this->getRandomProducts($manager) as $product) {
                /** @var Category $category */
                $category->addProduct($product);
            }

            $manager->persist($category);
        }

        $manager->flush();
    }

    private function getRandomProducts(ObjectManager $manager): array
    {
        $products = $manager->getRepository(Product::class)->findAll();
        shuffle($products);

        try {
            return array_slice($products,0, random_int(1, 5));
        } catch (Exception $e) {
            return [];
        }
    }

    private function getRandomCategories(ObjectManager $manager): array
    {
        $categories = $manager->getRepository(Category::class)->findAll();
        shuffle($categories);

        try {
            return array_slice($categories,0, random_int(1, 5));
        } catch (Exception $e) {
            return [];
        }
    }
}
