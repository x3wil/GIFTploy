<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Form;

use Entity\User;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, ['label' => 'form.name'])
            ->add('email', 'email', ['label' => 'form.email'])
            ->add('password', 'repeated', [
                'type' => 'password',
                'options' => [],
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'user.password.mismatch',
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'intention' => 'registration',
            'validation_groups' => ['Registration'],
        ]);
    }

    public function getName()
    {
        return 'user_registration';
    }

}
