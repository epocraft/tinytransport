<?php

namespace App\Form\Admin;

use App\Entity\Admin\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class LanguageType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'language'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.name', [], 'language'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('urlAlias', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.url_alias', [], 'language'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.url_alias', [], 'language'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('locale', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.locale', [], 'language'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.locale', [], 'language'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('translate', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.translate', [], 'language'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.translate', [], 'language'), 'UTF-8'),
                ],
                'help' => 'odkaz na překladový klíč',
                'required' => true,
            ])
            ->add('publication', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.publication'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('publication.select'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('publication.unpublish'), 'UTF-8') => '0',
                    mb_strtolower($this->translator->trans('publication.publish'), 'UTF-8') => '1',
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Language::class,
        ]);
    }
}
