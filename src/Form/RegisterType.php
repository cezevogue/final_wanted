<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nickname', TextType::class,[
                'required'=>false,
                'label'=>'Pseudo',
                'attr'=>[
                    'placeholder'=>'Saisissez votre pseudo'
                ]

            ])
            ->add('email', EmailType::class,[
                'required'=>false,
                'label'=>'Email',
                'attr'=>[
                    'placeholder'=>'Saisissez votre email'
                ]
            ])
            ->add('password', PasswordType::class,[
                'required'=>false,
                'label'=>'Mot de passe',
                'attr'=>[
                    'placeholder'=>'Saisissez un mot de passe'
                ]

            ])
            ->add('confirmPassword', PasswordType::class,[
                'required'=>false,
                'label'=>'Confirmation de mot de passe',
                'attr'=>[
                    'placeholder'=>'confirmez le mot de passe'
                ]

            ])
            ->add('valider', SubmitType::class,[
                'attr'=>[
                    'class'=>'mt-3 btn btn-primary'
                ]
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
