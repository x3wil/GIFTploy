<?php

namespace Form;

use Entity\Project;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type;

class ProjectFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'form.project.title',
                'attr' => [
                    'placeholder' => 'form.project.title',
                ],
            ])
            ->add('url', null, [
                'label' => 'form.project.url',
                'attr' => [
                    'placeholder' => 'form.project.url',
                ],
            ])
            //            ->add('branch', null, ['label' => 'form.project.branch', 'attr' => ['placeholder' => 'form.project.branch']])
            ->add('username', null, [
                'label' => 'form.project.username',
                'attr' => [
                    'placeholder' => 'form.project.username',
                ],
            ])
            ->add('password', Type\PasswordType::class, [
                'label' => 'form.project.password',
                'attr' => [
                    'placeholder' => 'form.project.password',
                ],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'intention' => 'project',
            'validation_groups' => ['Project'],
        ]);
    }

    public function getName()
    {
        return 'project';
    }
}