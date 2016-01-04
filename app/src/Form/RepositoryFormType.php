<?php

namespace Form;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type;

class RepositoryFormType extends AbstractType
{
    private $class = 'Entity\Repository';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'form.repository.title', 'attr' => ['placeholder' => 'form.repository.title']])
            ->add('url', null, ['label' => 'form.repository.url', 'attr' => ['placeholder' => 'form.repository.url']])
            ->add('branch', null, ['label' => 'form.repository.branch', 'attr' => ['placeholder' => 'form.repository.branch']])
            ->add('username', null, ['label' => 'form.repository.username', 'attr' => ['placeholder' => 'form.repository.username']])
            ->add('password', Type\PasswordType::class, ['label' => 'form.repository.password', 'attr' => ['placeholder' => 'form.repository.password']]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
            'intention'  => 'repository',
            'validation_groups' => ['Repository'],
        ]);
    }

    public function getName()
    {
        return 'repository';
    }
}