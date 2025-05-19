<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LastnameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastnameField', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => '', 'class' => 'form-control'],
            ]);
    }
}