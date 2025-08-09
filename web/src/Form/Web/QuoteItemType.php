<?php

namespace App\Form\Web;

use App\Entity\Web\QuoteItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 2],
                'label' => 'Popis položky',
            ])
            ->add('height', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'label' => 'Výška (cm)',
            ])
            ->add('width', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'label' => 'Šířka (cm)',
            ])
            ->add('depth', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'label' => 'Hloubka (cm)',
            ])
            ->add('weight', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'label' => 'Hmotnost (kg)',
            ]);
        // POZN: pole *_unit zatím vynechávám; doplníme po přidání entit LengthUnit/WeightUnit.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuoteItem::class,
        ]);
    }
}
