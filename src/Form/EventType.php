<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use PHPUnit\TextUI\CliArguments\Mapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use Symfonycasts\DynamicForms\DependentField;
use App\Form\DataTransformer\FileToStringTransformer;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'événement',
                'attr' => [
                    'class' => 'form-control rounded-pill',
                    'placeholder' => 'Nom de l\'événement'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control rounded',
                    'placeholder' => 'Description',
                    'rows' => 5
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Image de l\'événement',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'data-action' => "live#action:prevent",
                    'data-live-action-param' => "files|updatePicturePreview",
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'maxWidth' => 800,
                        'maxHeight' => 600,
                    ])
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => [
                    'class' => 'form-control rounded-pill',
                    'placeholder' => 'Lieu'
                ]
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label' => 'Nombre maximum de participants',
                'attr' => [
                    'class' => 'form-control rounded-pill',
                    'min' => 1,
                    'placeholder' => 'Nombre maximum de participants'
                ]
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'input' => 'datetime',
                'html5' => true,
                'model_timezone' => 'Europe/Paris',
                'view_timezone' => 'Europe/Paris',
                'attr' => [
                    'class' => 'form-control rounded-pill',
                    'placeholder' => 'Date de début'
                ]
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'input' => 'datetime',
                'html5' => true,
                'model_timezone' => 'Europe/Paris',
                'view_timezone' => 'Europe/Paris',
                'attr' => [
                    'class' => 'form-control rounded-pill',
                    'placeholder' => 'Date de fin'
                ]
            ])
            ->add('isPaid', ChoiceType::class, [
                'label' => '',
                'choices' => [
                    'Gratuit' => false,
                    'Payant' => true
                ],
                'attr' => [
                    'class' => 'form-select rounded-pill'
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Choisissez une catégorie',
                'attr' => [
                    'class' => 'form-select rounded-pill'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer l\'événement',
                'attr' => [
                    'class' => 'btn btn-action rounded-pill'
                ]
            ]);

        $builder->addDependent('price', 'isPaid', function (DependentField $field, ?bool $isPaid) {
            if ($isPaid === true) {
                $field->add(MoneyType::class, [
                    'required' => true,
                    'label' => 'Prix',
                    'currency' => '',
                    'attr' => [
                        'class' => 'form-control rounded-pill',
                        'placeholder' => 'Prix en €'
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
