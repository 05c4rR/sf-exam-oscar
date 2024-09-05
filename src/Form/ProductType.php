<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SpecialOffer;
use App\Entity\Stock;
use App\Entity\Tax;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('visible')
            ->add('onSale')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('tax', EntityType::class, [
                'class' => Tax::class,
                'choice_label' => 'name',
            ])
            ->add('specialOffer', EntityType::class, [
                'class' => SpecialOffer::class,
                'choice_label' => 'name',
            ])
            ->add('stockQuantity', IntegerType::class, [
                'label' => 'Stock Quantity',
                'mapped' => false, 
                'required' => true,
            ])
            ->add('images', FileType::class, [
                'label' => 'Upload Images',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
