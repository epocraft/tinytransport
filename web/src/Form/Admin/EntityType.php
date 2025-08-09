<?php

namespace App\Form\Admin;

use App\Entity\Admin\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Contracts\Translation\TranslatorInterface;

class EntityType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'entity'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.name', [], 'entity'), 'UTF-8'),
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'error.name.required'
                    ]),
                    new Length([
                        'min' => 3, 
                        'minMessage' => 'error.name.min',
                        'max' => 255,
                        'maxMessage' => 'error.name.max',
                    ]),
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entity::class,
        ]);
    }
}
