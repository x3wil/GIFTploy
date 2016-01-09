<?php

namespace Form;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type;

class EnvironmentFormType extends AbstractType
{

    private $class = 'Entity\Environment';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'form.environment.title', 'attr' => ['placeholder' => 'form.environment.title']])
            ->add('branch', null, ['label' => 'form.environment.branch', 'attr' => ['placeholder' => 'form.environment.branch']])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
            'intention' => 'environment',
            'validation_groups' => ['Environment'],
        ]);
    }

    public function getName()
    {
        return 'environment';
    }
}
