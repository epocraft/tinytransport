<?php

namespace App\Form\Web;

use App\Entity\Web\UserContact;
use App\Form\Web\Type\BooleanCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserContactType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('aresIco', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_in', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_in', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('aresSend', ButtonType::class, [
                'label' => mb_strtolower($this->translator->trans('label.search_in_ares', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'value' => '1'
                ]
            ])
            ->add('ciName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_name', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_name', [], 'supplier'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('ciIn', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_in', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_in', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciTin', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_tin', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_tin', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciVatPayer', BooleanCheckboxType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_vat_payer', [], 'supplier'), 'UTF-8'),
                'required' => false,
            ])
            ->add('ciBa', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_ba', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_ba', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciBc', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_bc', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_bc', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciIban', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_iban', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_iban', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciSwift', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_swift', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_swift', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciDuns', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_duns', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_duns', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciDataBox', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_data_box', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_data_box', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciWeb', UrlType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_web', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_web', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciPhone', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_phone', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_phone', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciMobile', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_mobile', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_mobile', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciFax', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_fax', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_fax', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciEmail', EmailType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_email', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_email', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('ciInvoicingEmail', EmailType::class, [
                'label' => mb_strtolower($this->translator->trans('label.ci_invoicing_email', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.ci_invoicing_email', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('biName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_name', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_name', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('biStreet', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_street', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_street', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('biCity', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_city', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_city', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('biZipcode', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_zipcode', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_zipcode', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('biCountry', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.bi_country', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.bi_country', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('diName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_name', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_name', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('diStreet', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_street', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_street', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('diCity', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_city', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_city', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('diZipcode', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_zipcode', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_zipcode', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('diCountry', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.di_country', [], 'supplier'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.di_country', [], 'supplier'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('discord', UrlType::class, [
                'label' => '<i class="mdi mdi-discord"></i> Discord',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('facebook', UrlType::class, [
                'label' => '<i class="mdi mdi-facebook"></i> Facebook',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('instagram', UrlType::class, [
                'label' => '<i class="mdi mdi-instagram"></i> Instagram',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('linkedin', UrlType::class, [
                'label' => '<i class="mdi mdi-linkedin"></i> LinkedIn',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('pinterest', UrlType::class, [
                'label' => '<i class="mdi mdi-pinterest"></i> Pinterest',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('snapchat', UrlType::class, [
                'label' => '<i class="mdi mdi-snapchat"></i> Snapchat',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('telegram', UrlType::class, [
                'label' => '<i class="mdi mdi-telegram"></i> Telegram',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('tiktok', UrlType::class, [
                'label' => '<i class="mdi mdi-tiktok"></i> TikTok',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('tumblr', UrlType::class, [
                'label' => '<i class="mdi mdi-tumblr"></i> Tumblr',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('x', UrlType::class, [
                'label' => '<i class="mdi mdi-x"></i> X',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('whatsapp', UrlType::class, [
                'label' => '<i class="mdi mdi-whatsapp"></i> WhatsApp',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
            ->add('youtube', UrlType::class, [
                'label' => '<i class="mdi mdi-youtube"></i> YouTube',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'https://',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserContact::class,
        ]);
    }
}
