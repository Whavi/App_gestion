<?php

namespace App\Form\addItem;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserFormProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identifiant', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minLength' => '2',
                    'maxLength' => '50'
                ],
                'label' => 'Numéro de série',
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => '50']),
                ]
                ])
                
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minLength' => '2',
                    'maxLength' => '50'
                ],
                'label' => 'Modèle',
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => '50']),
                ]
            ])
            // ->add('ref', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control',
            //         'minLength' => '2',
            //         'maxLength' => '50'
            //     ],
            //     'label' => 'Ref. log',
            //     'label_attr' => [
            //         'class' => 'form_label mt-4'
            //     ],
            //     'required' => true,
            //     'constraints' => [
            //         new Assert\NotBlank(),
            //         new Assert\Length(['min' => 2, 'max' => '50']),
            //     ]
            // ])

            ->add('category', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minLength' => '2',
                    'maxLength' => '50'
                ],
                'choices' =>[
                    'Ordinateur Fixe' => 'Ordinateur Fixe',
                    'Ordinateur Portable' => 'Ordinateur Portable',
                    'Souris' => 'Souris',
                    'Clavier' => 'Clavier',
                    'Imprimante' => 'Imprimante',
                    'Casque' => 'Casque',
                    'Chargeur' => 'Chargeur',
                    'Écran' => 'Écran',
                    'Scanner' => 'Scanner',
                    'Autre' => 'Autre'
                    
                ],
                'label' => 'Catégorie',
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => '50']),
                ]
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
            'data_class' => Product::class,
        ]);
    }
}
