<?php

namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationType extends AbstractType
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
                    'class' => 'form_label'
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
                    'class' => 'form_label'
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
                        'class' => 'form_label'
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(),
                        new Assert\Length(['min' => 2, 'max' => '180']),
                    ]


            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => "Mot de passe"
                ],
                'second_options' => [
                    'label' => "Confirmer le mot de passe"
                    ],
                    'invalid_message' => 'Les mots de passe ne correspondent pas',
                    'required' => true,
            ])
            ->add('Submit', SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-primary'
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => collaborateur::class,
        ]);
    }
}
