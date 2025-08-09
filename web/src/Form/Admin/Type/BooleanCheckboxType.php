<?php

namespace App\Form\Admin\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Form\Admin\DataTransformer\BooleanToIntTransformer;

class BooleanCheckboxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new BooleanToIntTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Default Label',
            'attr' => [
                'class' => 'form-check-input'
            ],
            'empty_data' => null,
            'required' => false,
        ]);
    }

    public function getParent()
    {
        return CheckboxType::class;
    }
}
