<?php

namespace Form;

use Entity\Environment;
use Entity\Project;
use GIFTploy\Git\Git;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EnvironmentFormType extends AbstractType
{

    /** @var \Entity\Project */
    private $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

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
                $disabledBranches = array_map(function (Environment $environment) {
                    return $environment->getBranch();
                }, $this->project->getEnvironments()->toArray());

                $branches = Git::getRemoteBranches($this->project->getUrl());

                $form->add('branch', Type\ChoiceType::class, [
                    'label' => 'form.environment.branch',
                    'choices' => array_combine($branches, $branches),
                    'choice_attr' => function ($branch) use ($disabledBranches) {
                        if (in_array($branch, $disabledBranches)) {
                            return ['disabled' => 'disabled'];
                        }

                        return [];
                    },
                    'empty_data' => null,
                    'empty_value' => 'form.environment.select',
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
