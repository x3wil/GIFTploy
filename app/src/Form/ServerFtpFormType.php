<?php

namespace Form;

use Entity\ServerFtp;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type;

class ServerFtpFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'form.server.ftp.title',
                'attr' => [
                    'placeholder' => 'form.server.ftp.title',
                ],
            ])
            ->add('host', null, [
                'label' => 'form.server.ftp.host',
                'attr' => [
                    'placeholder' => 'form.server.ftp.host',
                ],
            ])
            ->add('username', null, [
                'label' => 'form.server.ftp.username',
                'attr' => [
                    'placeholder' => 'form.server.ftp.username',
                ],
            ])
            ->add('password', Type\PasswordType::class, [
                'label' => 'form.server.ftp.password',
                'attr' => [
                    'placeholder' => 'form.server.ftp.password',
                ],
            ])
            ->add('port', Type\IntegerType::class, [
                'label' => 'form.server.ftp.port',
                'attr' => [
                    'placeholder' => 'form.server.ftp.port',
                ],
            ])
            ->add('path', null, [
                'label' => 'form.server.ftp.path',
                'attr' => [
                    'placeholder' => 'form.server.ftp.pathExample',
                ],
            ])
            ->add('passive', Type\CheckboxType::class, [
                'label' => 'form.server.ftp.passive',
                'attr' => [
                    'placeholder' => 'form.server.ftp.passive',
                ],
            ])
            ->add('timeout', Type\IntegerType::class, [
                'label' => 'form.server.ftp.timeout',
                'attr' => [
                    'placeholder' => 'form.server.ftp.timeout',
                ],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServerFtp::class,
            'intention' => 'server',
            'validation_groups' => ['ServerFtp'],
        ]);
    }

    public function getName()
    {
        return 'server';
    }
}