<?php

namespace App\Form\Admin;

use App\Entity\Admin\Project;
use App\Form\Admin\Type\BooleanCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('aresIco', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_in', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_in'), 'UTF-8'),
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('aresSend', ButtonType::class, [
                'label' => mb_strtolower($this->translator->trans('label.search_in_ares', [], 'project'), 'UTF-8'),
                'attr' => [
                    'value' => '1'
                ]
            ])
            ->add('ciName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_name', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_name', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => true,
            ])
            ->add('ciIn', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_in', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_in', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciTin', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_tin', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_tin', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciVatPayer', BooleanCheckboxType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_vat_payer', [], 'project'), 'UTF-8'),
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciBa', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_ba', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_ba', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciBc', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_bc', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_bc', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciIban', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_iban', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_iban', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciSwift', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_swift', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_swift', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciRegisteredRegister', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_registered_register', [], 'project'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('register.select'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('register.or'), 'UTF-8') => 'or',
                    mb_strtolower($this->translator->trans('register.zr'), 'UTF-8') => 'zr',
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciRegisteredOffice', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_registered_office', [], 'project'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('office.select'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('office.ks'), 'UTF-8') => 'ks',
                    mb_strtolower($this->translator->trans('office.mu'), 'UTF-8') => 'mu',
                    mb_strtolower($this->translator->trans('office.ou'), 'UTF-8') => 'ou',
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciRegisteredCity', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_registered_city', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_registered_city', [], 'project'), 'UTF-8'),
                ],
                'help' => mb_strtolower($this->translator->trans('help.ci_registered_city', [], 'project'), 'UTF-8'),
                'required' => false,
            ])
            ->add('ciRegisteredFileNumber', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_registered_file_number', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_registered_file_number', [], 'project'), 'UTF-8'),
                ],
                'help' => mb_strtolower($this->translator->trans('help.ci_registered_file_number', [], 'project'), 'UTF-8'),
                'required' => false,
            ])
            ->add('ciDuns', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_duns', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_duns', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciDataBox', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_data_box', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_data_box', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciWeb', UrlType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_web', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_web', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciPhoneCode', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_phone_code', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_phone_code', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciPhone', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_phone', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_phone', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciMobile', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_mobile', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_mobile', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciFax', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_fax', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_fax', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('ciEmail', EmailType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_email', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_email', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('biName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_name', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_name', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('biStreet', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_street', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_street', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('biCity', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_city', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_city', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('biZipcode', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_zipcode', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_zipcode', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('biCountry', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_country', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_country', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('diName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_name', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_name', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('diStreet', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_street', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_street', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('diCity', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_city', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_city', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('diZipcode', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_zipcode', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_zipcode', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('diCountry', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_country', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_country', [], 'project'), 'UTF-8'),
                ],
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => mb_strtolower($this->translator->trans('label.image', [], 'image'), 'UTF-8'),
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'validator.please_upload_a_valid_image',
                    ])
                ],
                'mapped' => false,
            ])
            ->add('discord', UrlType::class, [
                'label' => 'Discord',
                'required' => false,
            ])
            ->add('facebook', UrlType::class, [
                'label' => 'Facebook',
                'required' => false,
            ])
            ->add('instagram', UrlType::class, [
                'label' => 'Instagram',
                'required' => false,
            ])
            ->add('linkedin', UrlType::class, [
                'label' => 'LinkedIn',
                'required' => false,
            ])
            ->add('pinterest', UrlType::class, [
                'label' => 'Pinterest',
                'required' => false,
            ])
            ->add('snapchat', UrlType::class, [
                'label' => 'Snapchat',
                'required' => false,
            ])
            ->add('telegram', UrlType::class, [
                'label' => 'Telegram',
                'required' => false,
            ])
            ->add('tiktok', UrlType::class, [
                'label' => 'Tiktok',
                'required' => false,
            ])
            ->add('tumblr', UrlType::class, [
                'label' => 'Tumblr',
                'required' => false,
            ])
            ->add('x', UrlType::class, [
                'label' => 'X',
                'required' => false,
            ])
            ->add('whatsapp', UrlType::class, [
                'label' => 'Whatsapp',
                'required' => false,
            ])
            ->add('youtube', UrlType::class, [
                'label' => 'YouTube',
                'required' => false,
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
            ->add('maintenance', BooleanCheckboxType::class, [
                'label' => mb_strtolower($this->translator->trans('label.maintenance', [], 'project'), 'UTF-8'),
                'translation_domain' => 'project',
                'required' => false,
            ])
            ->add('maintenanceText', TextareaType::class, [
                'label' => mb_strtolower($this->translator->trans('label.maintenance_text', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.maintenance_text', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
