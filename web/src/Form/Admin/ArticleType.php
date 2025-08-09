<?php

namespace App\Form\Admin;

use App\Entity\Admin\Article;
use App\Entity\Admin\Language;
use App\Entity\Admin\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.title', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.title', [], 'article'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('metaDescription', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.meta_description', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.meta_description', [], 'article'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('metaKeywords', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.meta_keywords', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.meta_keywords', [], 'article'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('shortName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.short_name', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.short_name', [], 'article'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.name', [], 'article'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('urlAlias', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.url_alias', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.url_alias', [], 'article'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('perex', TextareaType::class, [
                'label' => mb_strtolower($this->translator->trans('label.perex', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.perex', [], 'article'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('text', TextareaType::class, [
                'label' => mb_strtolower($this->translator->trans('label.text', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.text', [], 'article'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('paidText', TextareaType::class, [
                'label' => mb_strtolower($this->translator->trans('label.paid_text', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.paid_text', [], 'article'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('tag', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.tag', [], 'article'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.tag', [], 'article'), 'UTF-8'),
                ],
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
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => mb_strtolower($this->translator->trans('label.language', [], 'language'), 'UTF-8'),
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
            'data_class' => Article::class,
        ]);
    }
}
