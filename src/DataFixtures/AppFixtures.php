<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\SpecialOffer;
use App\Entity\Stock;
use App\Entity\Tax;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const NB_PRODUCTS = 100;
    private const DEFAULT_IMAGE_PATH = 'uploads/images/tarteaucitron200x200-66d97a1028c2e.jpg';

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

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

            $randomCategory = $categories[random_int(0, count($categories) - 1)];

            $stock
                ->setProduct($product)
                ->setQuantity($faker->randomNumber(3));

            $product
                ->setDescription($faker->realText($maxNbChars = 200, $indexSize = 2))
                ->setPrice($faker->randomFloat(2, 10, 300))
                ->setVisible($faker->boolean())
                ->setOnSale($faker->boolean())
                ->setCategory($randomCategory)
                ->setName($randomCategory->getName())
                ->setTax($standardVAT)
                ->setStock($stock);

                $image = new Image();
                $image->setImageName(self::DEFAULT_IMAGE_PATH);
                $image->setProduct($product);

                $manager->persist($image);

            if($product->isOnSale()){
                $product->setSpecialOffer($specialSale);
            }
            
            $manager->persist($product);
        }

        $admin = new User;
        $admin
            ->setEmail('admin@test.com')
            ->setPassword($this->hasher->hashPassword($admin, 'admin'))
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $user = new User;
        $user
            ->setEmail('user@test.com')
            ->setPassword($this->hasher->hashPassword($user, 'user'));

        $manager->persist($user);

        $manager->flush();
    }
}
