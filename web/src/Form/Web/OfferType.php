<?php

namespace App\Form\Web;

use App\Entity\Web\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', NumberType::class, [
                'label' => 'Cena celkem',
                'scale' => 2,
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'Měna',
                'choices' => [
                    'CZK' => 'CZK',
                    'EUR' => 'EUR',
                ],
                'placeholder' => false,
            ])
            ->add('message', TextareaType::class, [
                'required' => false,
                'label' => 'Zpráva zákazníkovi',
                'attr' => ['rows' => 4],
            ]);
        // quote a serviceProvider nastavujeme v controlleru – nejsou ve formuláři
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Offer::class]);
    }
}
