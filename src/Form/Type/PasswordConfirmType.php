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
use Symfony\Component\Validator\Constraints\Regex;

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
                        'class' => 'form-control',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le mot de passe est obligatoire',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 4096,
                            'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères',
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                            'message' => 'Le mot de passe doit contenir au minimum une majuscule, un chiffre et un caractère spécial.',
                        ]),
                        new PasswordStrength([
                            'message' => 'Le mot de passe est trop faible. Utilisez des lettres majuscules et minuscules, des chiffres, et des symboles.',
                            'minScore' => PasswordStrength::STRENGTH_WEAK,
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a été compromis lors d\'une fuite de données. Veuillez en choisir un autre.',
                        ]),
                    ],

                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Votre mot de passe',
                        'class' => 'form-control',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirmez votre mot de passe',
                        'class' => 'form-control',
                    ],
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                'mapped' => false,
            ]);
    }
}
