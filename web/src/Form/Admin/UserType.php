<?php

namespace App\Form\Admin;

use App\Entity\Admin\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => mb_strtolower($this->translator->trans('label.email', [], 'user'), 'UTF-8'),
                'attr' => [
                    'placeholder' => mb_strtolower($this->translator->trans('placeholder.email', [], 'user'), 'UTF-8'),
                ],
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'label' => mb_strtolower($this->translator->trans('label.password', [], 'user'), 'UTF-8'),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                        'groups' => ['password_change']  // Tato validace se použije pouze pokud je skupina aktivována
                    ]),
                ],
                'help' => mb_strtolower($this->translator->trans('help.password', [], 'user'), 'UTF-8'),
                'help_html' => true,
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => mb_strtolower($this->translator->trans('label.confirm_password', [], 'user'), 'UTF-8'),
                'constraints' => [
                    new Callback(function ($confirmPassword, ExecutionContextInterface $context) {
                        $form = $context->getRoot(); // Získání celého formuláře
                        $password = $form->get('password')->getData(); // Získání hodnoty pole 'password'
                        
                        if ($password && $confirmPassword !== $password) {
                            $context->buildViolation('Passwords do not match.')
                                ->atPath('confirmPassword')
                                ->addViolation();
                        }
                    }),
                ],
                'required' => false,
                'mapped' => false,
                'help' => mb_strtolower($this->translator->trans('help.confirm_password', [], 'user'), 'UTF-8'),
                'help_html' => true,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.roles', [], 'user'), 'UTF-8'),
                'choices' => [
                    'Superadmin' => 'ROLE_SUPERADMIN',
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER'
                ],
                'multiple' => true,
                'expanded' => true, // Vytvoří checkboxy místo dropdownu
            ])
            ->add('publication', ChoiceType::class, [
                'label' => mb_strtolower($this->translator->trans('label.publication', [], 'user'), 'UTF-8'),
                'choices' => [
                    mb_strtolower($this->translator->trans('publication.select'), 'UTF-8') => '',
                    mb_strtolower($this->translator->trans('publication.unpublish'), 'UTF-8') => '0',
                    mb_strtolower($this->translator->trans('publication.publish'), 'UTF-8') => '1',
                ],
                'required' => true,
            ])
            ->add('contact', UserContactType::class, [
                'label' => 'Contact',
                'property_path' => 'userUserContact',
            ])
            ->add('settings', UserSettingsType::class, [
                'label' => 'Settings',
                'property_path' => 'userUserSettings',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
