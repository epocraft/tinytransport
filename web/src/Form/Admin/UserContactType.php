<?php

namespace App\Form\Admin;

use App\Entity\Admin\User;
use App\Entity\Admin\UserContact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserContactType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleBefore', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.title_before', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.title_before', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.firstname', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.firstname', [], 'user'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('surname', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.surname', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.surname', [], 'user'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('titleAfter', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.title_after', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.title_after', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('jobPosition', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.job_position', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.job_position', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.phone', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.phone', [], 'user'), 'UTF-8'),
                ],
                'required' => false,
            ])
            ->add('mobile', TextType::class, [
                'label' => mb_strtolower($this->translator->trans('label.mobile', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.mobile', [], 'user'), 'UTF-8'),
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
