<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

             $builder
                 ->add('src', FileType::class, [
                     'required'=>false,
                     'label'=>'Fichier Photo/Vidéo à uploader',
                     'attr'=>[
                         'onChange'=>'loadFile(event)'
                     ],
                     'constraints'=>[
                         new File(['maxSize'=>'2000k','maxSizeMessage'=>'Fichier trop volumineux, maximum 2MO', 'mimeTypes'=>['image/jpg','image/jpeg','image/webp', 'image/png'], 'mimeTypesMessage'=>'Les formats autorisés sont: image/jpg,image/jpeg,image/webp, image/png '])
                     ]


                 ])
                 ->add('Valider', SubmitType::class)

             ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
