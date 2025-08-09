<?php

namespace App\Form\Admin;

use App\Entity\Admin\Project;
use App\Entity\Admin\ProjectContact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectContactType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piName', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.pi_name', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.pi_name', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('piPhone', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.pi_phone', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.pi_phone', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('piMobile', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.pi_mobile', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.pi_mobile', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('piFax', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.pi_fax', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.pi_fax', [], 'project'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('piEmail', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.pi_email', [], 'project'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.pi_email', [], 'project'), 'UTF-8'),
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectContact::class,
        ]);
    }
}
