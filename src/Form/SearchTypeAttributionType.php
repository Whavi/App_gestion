<?php

namespace App\Form;

use App\Entity\Attribution;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchTypeAttributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
        
            ->add('product', CollectionType::class, array(
                'entry_type' => new Product(),
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'by_reference' => FALSE,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Attribution::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}
