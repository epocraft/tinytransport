<?php

namespace App\Form\Web;

use App\Entity\Web\Category;
use App\Entity\Web\Quote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Typ služby',
                'placeholder' => '— vyberte —',
            ])
            ->add('name', TextType::class, [
                'label' => 'Název poptávky',
            ])

            // NAKLÁDKA
            ->add('loadingAddress', TextType::class, [
                'label' => 'Adresa nakládky',
            ])
            ->add('loadingAddressAssistance', CheckboxType::class, [
                'required' => false,
                'label' => 'Potřebuji asistenci při nakládce',
            ])
            ->add('loadingAddressAssistanceNumberOfPersons', IntegerType::class, [
                'required' => false,
                'label' => 'Počet pomocníků (nakládka)',
            ])
            ->add('loadingAddressFloor', IntegerType::class, [
                'required' => false,
                'label' => 'Podlaží (nakládka)',
            ])
            ->add('loadingAddressLift', CheckboxType::class, [
                'required' => false,
                'label' => 'Výtah (nakládka)',
            ])
            ->add('loadingAddressWidthOfStaircase', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'label' => 'Šířka schodiště (m) – nakládka',
            ])
            ->add('loadingAddressRamp', CheckboxType::class, [
                'required' => false,
                'label' => 'Rampy / nájezdy (nakládka)',
            ])

            // VYKLÁDKA
            ->add('unloadingAddress', TextType::class, [
                'label' => 'Adresa vykládky',
            ])
            ->add('unloadingAddressAssistance', CheckboxType::class, [
                'required' => false,
                'label' => 'Potřebuji asistenci při vykládce',
            ])
            ->add('unloadingAddressAssistanceNumberOfPersons', IntegerType::class, [
                'required' => false,
                'label' => 'Počet pomocníků (vykládka)',
            ])
            ->add('unloadingAddressFloor', IntegerType::class, [
                'required' => false,
                'label' => 'Podlaží (vykládka)',
            ])
            ->add('unloadingAddressLift', CheckboxType::class, [
                'required' => false,
                'label' => 'Výtah (vykládka)',
            ])
            ->add('unloadingAddressWidthOfStaircase', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'label' => 'Šířka schodiště (m) – vykládka',
            ])
            ->add('unloadingAddressRamp', CheckboxType::class, [
                'required' => false,
                'label' => 'Rampy / nájezdy (vykládka)',
            ])

            ->add('deliveryTimeframe', TextType::class, [
                'required' => false,
                'label' => 'Časové okno doručení (volitelně)',
                'help' => 'Např. „příští týden“, „do 3 dnů“, „nejlépe 12–16 hod“.',
            ])

            // POLOŽKY
            ->add('items', CollectionType::class, [
                'entry_type' => QuoteItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => 'Položky',
                'required' => false,
            ])

            ->add('note', TextareaType::class, [
                'required' => false,
                'label' => 'Poznámka',
                'attr' => ['rows' => 3],
                'mapped' => false, // pokud chceš doplnit do entity, přejmenuj a namapuj
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Quote::class]);
    }
}
