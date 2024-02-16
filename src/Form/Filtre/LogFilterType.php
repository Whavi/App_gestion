<?php

namespace App\Form;

use App\Entity\LogEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Entry' => 0,
                    'Creation' => 1,
                    'Edit' => 2,
                    'Delete' => 3,
                    'Recherche' => 4,
                ],
                'label' => 'Niveau : ',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-check form-check-block'
                ],
                'expanded' => true,
                'required' => false,
            ])
            ->add('channel', ChoiceType::class, [
                'choices' => [
                    'Attribution' => 'ATTRIBUTION',
                    'Département' => 'DÉPARTEMENT',
                    'Utilisateur' => 'USER',
                    'Collaborateur' => 'COLLABORATEUR',
                    'Produit' => 'PRODUIT',
                    'Log' => 'LOG',
                ],
                'label' => 'Catégorie : ',
                'label_attr' => [
                    'class' => 'form-label mt-1'
                ],
                'attr' => [
                    'class' => 'form-check form-check-block'
                ],
                'expanded' => true,
                'required' => false,
            ])

            ->add('createdAt', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form_label mt-1'
                ], 
                'label' => 'Filtre date : ',
                'data_class' => null,
                'data' => new \DateTime(), // Définit la date par défaut sur aujourd'hui
                'required' => false,
                
            ])
            ->add('Submit', SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LogEntry::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}
