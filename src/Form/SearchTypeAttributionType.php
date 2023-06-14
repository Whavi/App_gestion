<?php

namespace App\Form;

use App\Model\SearchDataAttribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchTypeAttributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
        
        ->add('nom', SearchType::class, [
            'attr' => [
                'placeholder' => 'Recherche par nom ...',
            ],
            
            'empty_data' => '',
            'required' => false
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchDataAttribution::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}
