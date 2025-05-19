<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Form\Type\FirstnameType;
use App\Form\Type\LastnameType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse email',
                    ]),
                    new Email([
                        'message' => 'Veuillez entrer une adresse email valide',
                    ]),
                ],
                'label' => 'Email',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => ' ',
                ],
            ])
            ->add('firstname', FirstnameType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => ' ',
                ],
            ])
            ->add('lastname', LastnameType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => ' ',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
        ]);
    }
}
