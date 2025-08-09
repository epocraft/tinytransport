<?php

namespace App\Form\Admin;

use App\Entity\Admin\Project;
use App\Entity\Admin\ProjectCookieConsent;
use App\Form\Admin\Type\BooleanCheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectCookieConsentType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consentType', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.consent_type', [], 'project'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('consent_type.select', [], 'project'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('consent_type.implied', [], 'project'), 'UTF-8') => 'implied',
                    mb_strtolower($this->translator->trans('consent_type.express', [], 'project'), 'UTF-8') => 'express',
                ],
                'required' => true,
            ])
            ->add('noticeBannerType', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.notice_banner_type', [], 'project'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('notice_banner_type.select', [], 'project'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('notice_banner_type.simple', [], 'project'), 'UTF-8') => 'simple',
                    mb_strtolower($this->translator->trans('notice_banner_type.headline', [], 'project'), 'UTF-8') => 'headline',
                    mb_strtolower($this->translator->trans('notice_banner_type.interstitial', [], 'project'), 'UTF-8') => 'interstitial',
                    mb_strtolower($this->translator->trans('notice_banner_type.standalone', [], 'project'), 'UTF-8') => 'standalone',
                ],
                'required' => true,
            ])
            ->add('palette', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.palette', [], 'project'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('palette.select', [], 'project'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('palette.light', [], 'project'), 'UTF-8') => 'light',
                    mb_strtolower($this->translator->trans('palette.dark', [], 'project'), 'UTF-8') => 'dark',
                ],
                'required' => true,
            ])
            ->add('noticeBannerRejectButtonHide', BooleanCheckboxType::class, [
                'label' => mb_strtolower($this->translator->trans('label.notice_banner_reject_button_hide', [], 'project'), 'UTF-8'),
                'required' => false,
            ])
            ->add('preferencesCenterCloseButtonHide', BooleanCheckboxType::class, [
                'label' => mb_strtolower($this->translator->trans('label.preferences_center_close_button_hide', [], 'project'), 'UTF-8'),
                'required' => false,
            ])
            ->add('pageRefreshConfirmationButtons', BooleanCheckboxType::class, [
                'label' => mb_strtolower($this->translator->trans('label.page_refresh_confirmation_buttons', [], 'project'), 'UTF-8'),
                'required' => false,
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'ciName',
                'label' => mb_strtolower($this->translator->trans('label.project', [], 'project'), 'UTF-8'),
                'placeholder' => mb_strtolower($this->translator->trans('placeholder.select'), 'UTF-8'),
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
            'data_class' => ProjectCookieConsent::class,
        ]);
    }
}
