<?php

namespace App\Form;

use App\Entity\Collaborateur;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserFormCollaborateurType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minLength' => '2',
                'maxLength' => '50'
            ],
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 2, 'max' => '50']),
            ]
        ])
        ->add('prenom', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minLength' => '2',
                'maxLength' => '50'
            ],
            'label' => 'Prénom',
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 2, 'max' => '50']),
            ]
        ])


        ->add('email', EmailType::class,[
            'attr' => [
                'class' => 'form-control',
                'minLength' => '2',
                'maxLength' => '180'
                ],
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'form_label mt-4'
                ],
                'constraints' => [
                new Assert\NotBlank(),
                new Assert\Email(),
                new Assert\Length(['min' => 2, 'max' => '180'])
            ] 
            ])

            ->add('departement', EntityType::class, [
            'class' => Departement::class,
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form_label mt-4'
            ],
            
            'query_builder' => function (DepartementRepository $dr) {
                return $dr->createQueryBuilder('d')
                    ->orderBy('d.nom', 'ASC');
            },
            'choice_value' => function (?Departement $entity) {
                return $entity ? $entity->getId() : '';
            },

            'choice_label' => 'nom',

            'placeholder' => 'Choisissez un département',
            'required' => true,    
                
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
            'data_class' => Collaborateur::class,
        ]);
    }
}
