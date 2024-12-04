<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PasswordConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'toggle' => true,
                    'use_toggle_form_theme' => false,
                    'always_empty' => false,
                    'hidden_label' => 'Hide',
                    'visible_label' => 'Show',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 4096,
                        ]),
                        new PasswordStrength(),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a été compromis. Veuillez en choisir un autre.',
                        ]),

                    ],
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Choisissez un mot de passe',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirmez votre mot de passe',
                    ],
                    'invalid_message' => 'Les mots de passe ne correspondent pas.',
                    'mapped' => false,
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirmez votre mot de passe',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'mapped' => false,
            ]);
    }
}
