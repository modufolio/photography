<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', TextType::class, [
                'attr' => [
                    'placeholder' => 'username',
                ],
            ])
            ->add('_password', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'password',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     * https://stackoverflow.com/questions/28789109/can-the-symfony2-form-builder-be-used-for-the-login-form.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
            'csrf_token_id' => 'authenticate', // Matches security.yml
            'csrf_protection' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
