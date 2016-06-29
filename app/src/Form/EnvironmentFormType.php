<?php

namespace Form;

use Entity\Environment;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EnvironmentFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'form.environment.title',
                'attr' => [
                    'placeholder' => 'form.environment.title',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Entity\Environment $environment */
            $environment = $event->getData();
            $form = $event->getForm();
            
            if ($environment !== null) {
                $form->add('branch', null, [
                    'label' => 'form.environment.branch',
                    'attr' => [
                        'placeholder' => 'form.environment.branch',
                    ],
                    'disabled' => true,
                ]);
            } else {
                $form->add('branch', null, [
                    'label' => 'form.environment.branch',
                    'attr' => [
                        'placeholder' => 'form.environment.branch',
                    ],
                ]);
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Environment::class,
            'intention' => 'environment',
            'validation_groups' => ['Environment'],
        ]);
    }

    public function getName()
    {
        return 'environment';
    }
}
