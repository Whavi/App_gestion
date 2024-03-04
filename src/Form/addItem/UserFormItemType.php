<?php

namespace App\Form\addItem;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserFormItemType extends AbstractType
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
                'required' => true,
                'constraints' => [
                new Assert\NotBlank(),
                new Assert\Email(),
                new Assert\Length(['min' => 2, 'max' => '180'])
            ] 
            ])
            

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'invalid_message' => 'Les mots de passe ne correspondent pas',
                    'label' => "Mot de passe",
                    'label_attr' => [
                        'class' => 'form_label mt-4 ',
                    ]
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'label' => "Confirmation du mot de passe",
                    'label_attr' => [
                        'class' => 'form_label mt-4',
                    ],
                    'invalid_message' => 'Les mots de passe ne correspondent pas',
                    'required' => true,
            ]])

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
            'data_class' => User::class,
        ]);
    }
}
