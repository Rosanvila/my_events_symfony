<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FirstnameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstnameField', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => '', 'class' => 'form-control'],
            ]);
    }
}