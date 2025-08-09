<?php

namespace App\Form\Admin;

use App\Entity\Admin\Language;
use App\Entity\Admin\Project;
use App\Entity\Admin\ProjectText;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectTextType extends AbstractType
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
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.name', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.description', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.description', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'ciName',
                'label' => mb_strtolower($this->translator->trans('label.project', [], 'project'), 'UTF-8'),
                'placeholder' => mb_strtolower($this->translator->trans('placeholder.select'), 'UTF-8'),
                'required' => true,
            ])
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => mb_strtolower($this->translator->trans('label.language', [], 'project'), 'UTF-8'),
                'placeholder' => mb_strtolower($this->translator->trans('placeholder.select'), 'UTF-8'),
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectText::class,
        ]);
    }
}
