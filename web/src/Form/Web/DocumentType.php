<?php

namespace App\Form\Web;

use App\Entity\Web\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentType extends AbstractType
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
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'document'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.name', [], 'document'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => mb_strtolower($this->translator->trans('label.description', [], 'document'), 'UTF-8'),
                'attr' => [
                    'placeholder' =>  mb_strtolower($this->translator->trans('placeholder.description', [], 'document'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('version', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.version', [], 'document'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.version', [], 'document'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('fileName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.file_name', [], 'document'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.file_name', [], 'document'), 'UTF-8'),
                ],
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
