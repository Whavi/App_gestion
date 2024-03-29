<?php

namespace App\Form\addItem;

use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Product;
use App\Repository\CollaborateurRepository;
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormAttributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('collaborateur', EntityType::class, [
                'class' => Collaborateur::class,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],
                'query_builder' => function (CollaborateurRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                    },
                'placeholder' => 'Choisissez un collaborateur',
                'required' => true,   ]
            )
            ->add('Product', EntityType::class, [
                'class' => Product::class,
                'constraints' => new NotBlank(['message' => 'Please enter a Product.']),
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],

                'query_builder' => function (ProductRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.category', 'ASC');
                    },

                'placeholder' => 'Choisissez un produit',
                'required' => true, ]
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
                'data' => new \DateTime(), // Définit la date par défaut sur aujourd'hui
                ])

            ->add('dateRestitution', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter datetime.']),
                ],
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
                'empty_data' => "",
                ])
            ->add('remarque', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],
                'label' => 'État du matériel',
                'required' => false,
                'empty_data' => "",
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
