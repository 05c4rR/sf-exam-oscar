<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SpecialOffer;
use App\Entity\Stock;
use App\Entity\Tax;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private const NB_PRODUCTS = 100;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $categoryNames = [
            'Chaussures',
            'Sac à dos',
            'Veste',
            'Pantalons',
            'Accessoires'
        ];

        $standardVAT = new Tax();
        $standardVAT
            ->setName('T.V.A')
            ->setValue(1.30);
        $manager->persist($standardVAT);


        $specialSale = new SpecialOffer();
        $specialSale
            ->setName('Promotion d\'été')
            ->setValue(1.5)
            ->setStartDate($faker->dateTime())
            ->setEndDate($faker->dateTime());
        $manager->persist($specialSale);



        for($i = 0; $i < count($categoryNames); $i++){
            $category = new Category();
            $category->setName($categoryNames[$i]);

            $manager->persist($category);
            $categories[] = $category;
        }

        for($i = 0; $i < self::NB_PRODUCTS; $i++){
            $product = new Product();
            $stock = new Stock();

            $stock
                ->setProduct($product)
                ->setQuantity($faker->randomNumber(3));

            $product
                ->setName($faker->word())
                ->setDescription($faker->paragraph())
                ->setPrice($faker->randomFloat())
                ->setVisible($faker->boolean())
                ->setOnSale($faker->boolean())
                ->setCategory($categories[random_int(0,count($categories)-1)])
                ->setTax($standardVAT)
                ->setStock($stock);

            if($product->isOnSale()){
                $product->setSpecialOffer($specialSale);
            }
            
            $manager->persist($product);
        }

        $manager->flush();
    }
}
