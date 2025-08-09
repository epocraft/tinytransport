<?php

namespace App\Form\Admin;

use App\Entity\Admin\Language;
use App\Entity\Admin\User;
use App\Entity\Admin\UserSettings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateFormat', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.date_format', [], 'user'), 'UTF-8'),
                'required' => true,
            ])
            ->add('dateTimeFormat', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.date_time_format', [], 'user'), 'UTF-8'),
                'required' => true,
            ])
            ->add('htmlSignature', TextareaType::class, [
                'label' => mb_strtolower($this->translator->trans('label.html_signature', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.html_signature', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('separatorOfThousands', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.separator_of_thousands', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.separator_of_thousands', [], 'user'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('decimalPoint', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.decimal_point', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.decimal_point', [], 'user'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => mb_strtolower($this->translator->trans('label.preffered_language', [], 'user'), 'UTF-8'),
                'placeholder' => mb_strtolower($this->translator->trans('placeholder.select'), 'UTF-8'),
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSettings::class,
        ]);
    }
}
