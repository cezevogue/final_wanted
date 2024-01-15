<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' =>  ['label' => 'Confirmez votre mot de passe'],
            'invalid_message' =>  'Les mot de passe ne correspondent pas.',
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
             'mapped' => false,
             'attr' => ['autocomplete' => 'nouveau mot de passe'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrez votre mot de passe',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit contenir minimum {{ limit }} caractères',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096
                ]),
            ],
        ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
