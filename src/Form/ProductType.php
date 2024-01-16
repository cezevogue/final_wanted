<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Titre du produit',
                'attr' => [
                    'placeholder' => "Saisissez le titre produit"
                ]


            ])
            ->add('price', TextType::class, [
                'required' => false,
                'label' => 'Prix du produit',
                'attr' => [
                    'placeholder' => "Saisissez le prix produit"
                ]


            ])
            ->add('description',TextareaType::class, [
                'required'=>false,
                'label'=>'description du produit',
                'attr'=>[
                    'placeholder'=>"Saisissez le titre produit"
                ]


            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
                'label' => 'Catégorie',
                'placeholder' => 'Saisissez la catégorie'
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'label' => 'Tags',
                'choice_label' => 'title',
                'multiple' => true,
                'placeholder' => 'Saisissez les tags en liens avec le produit',
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('Valider', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
