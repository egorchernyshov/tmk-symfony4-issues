<?php

namespace App\Tests\Functional\Controller;

use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testProductsList()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/product/');
        $productId = $crawler->filter('tbody tr td')->first()->text();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Product index');

        return $productId;
    }

    /**
     * @depends testProductsList
     */
    public function testProductsShow(string $id)
    {
        $client = static::createClient();
        $client->request('GET', "/product/{$id}");

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Product details');
    }

    /**
     * @depends testProductsList
     */
    public function testProductsEdit(string $id)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', "/product/{$id}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Edit Product');

        $form = $crawler->selectButton('Update')->form();
        $client->submit($form, [
            'product[title]' => $newTitle = Factory::create()->text(12)
        ]);

        self::assertEmailCount(1);

        $email = self::getMailerMessage(0);
        self::assertEmailHeaderSame($email, 'To', 'app@test');
        self::assertEmailHeaderSame($email, 'Subject', 'Product was updated');
        self::assertEmailTextBodyContains($email, $newTitle);
    }

    public function testProductNew()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/product/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create new Product');

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'product[title]' => $title = Factory::create()->text(11),
            'product[price]' => $price = Factory::create()->randomFloat(2),
            'product[eId]' => Factory::create()->numberBetween(),
        ]);

        self::assertEmailCount(1);

        $email = self::getMailerMessage(0);
        self::assertEmailHeaderSame($email, 'To', 'app@test');
        self::assertEmailHeaderSame($email, 'Subject', 'Product was added');
        self::assertEmailTextBodyContains($email, $title);
        self::assertEmailTextBodyContains($email, $price);
    }
}
