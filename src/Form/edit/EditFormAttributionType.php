<?php

namespace App\Form;

use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditFormAttributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('collaborateur', EntityType::class, [
            'class' => Collaborateur::class,
            'constraints' => new NotBlank(['message' => 'Please enter a collaborateur.']),
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            'placeholder' => 'Choisissez un collaborateur',
            'required' => true,   ]
        )
        ->add('Product', EntityType::class, [
            'class' => Product::class,
            'constraints' => new NotBlank(['message' => 'Please enter a product.']),
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            'placeholder' => 'Choisissez un produit',
            'required' => false, ]
        )

        ->add('dateAttribution', DateType::class, [
            'widget' => 'single_text',
            'constraints' => new NotBlank(['message' => 'Please enter datetime.']),
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            ])

        ->add('dateRestitution', DateType::class, [
            'widget' => 'single_text',
            'constraints' => new NotBlank(['message' => 'Please enter datetime.']),
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            ])
        ->add('descriptionProduct', TextType::class, [
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            'label' => 'Description du produit',
            'required' => false,
            ])
        ->add('remarque', TextType::class, [
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            'label' => 'Remarque du produit',
            'required' => false
            ])
        ->add('Submit', SubmitType::class,[
            'attr' => [
                'class' => 'btn btn-primary mt-4'
                ]
            ])    
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Attribution::class,
        ]);
    }
}
