<?php

namespace App\Form\Web;

use App\Entity\Web\UserDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserDocumentType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('insuranceLiability', FileType::class, [
                'label' => 'Pojištění odpovědnosti',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('insuranceLiabilityValidTo', DateType::class, [
                'label' => mb_strtolower($this->translator->trans('label.insurance_liability_valid_to', [], 'user'), 'UTF-8'),
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.insurance_liability_valid_to', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('insuranceTransport', FileType::class, [
                'label' => 'Pojištění o přepravě',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('insuranceTransportValidTo', DateType::class, [
                'label' => mb_strtolower($this->translator->trans('label.insurance_transport_valid_to', [], 'user'), 'UTF-8'),
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.insurance_transport_valid_to', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('idCardFront', FileType::class, [
                'label' => 'Přední strana OP',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('idCardBack', FileType::class, [
                'label' => 'Zadní strana OP',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('idCardValidTo', DateType::class, [
                'label' => mb_strtolower($this->translator->trans('label.id_card_valid_to', [], 'user'), 'UTF-8'),
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.id_card_valid_to', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('driverLicenseFront', FileType::class, [
                'label' => 'Přední strana ŘP',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('driverLicenseBack', FileType::class, [
                'label' => 'Zadní strana ŘP',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('driverLicenseValidTo', DateType::class, [
                'label' => mb_strtolower($this->translator->trans('label.driver_license_valid_to', [], 'user'), 'UTF-8'),
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.driver_license_valid_to', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('tradeLicense', FileType::class, [
                'label' => 'Živnostenský list',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Povoleno je pouze nahrávání souborů typu JPG, JPEG, PNG a PDF.',
                        'maxSizeMessage' => 'Soubor je příliš velký (max. {{ limit }} MB).',
                    ])
                ],
            ])
            ->add('tradeLicenseValidTo', DateType::class, [
                'label' => mb_strtolower($this->translator->trans('label.trade_license_valid_to', [], 'user'), 'UTF-8'),
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.trade_license_valid_to', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDocument::class,
        ]);
    }
}