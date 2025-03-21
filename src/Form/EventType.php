<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
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
use Symfony\Component\Validator\Constraints\File;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use Symfonycasts\DynamicForms\DependentField;

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
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
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
                'label' => 'Type d\'événement',
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
                'choice_label' => 'description',
                'label' => 'Catégorie',
                'placeholder' => 'Choisissez une catégorie',
                'attr' => [
                    'class' => 'form-select rounded-pill'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer l\'événement',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);

        $builder->addDependent('price', 'isPaid', function (DependentField $field, ?bool $isPaid) {
            if ($isPaid === true) {
                $field->add(MoneyType::class, [
                    'required' => true,
                    'label' => 'Prix',
                    'currency' => 'EUR',
                    'attr' => [
                        'class' => 'form-control rounded-pill',
                        'placeholder' => 'Prix'
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
