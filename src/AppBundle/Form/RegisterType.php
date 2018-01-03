<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nickname', TextType::class, [
                'attr' => [ 'placeholder' => 'Your nickname'],
                'label' => 'Nickname: '
            ])
            ->add('username', TextType::class, [
                'attr' => [ 'placeholder' => 'Your username'],
                'label' => 'Username: '
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'attr' => [ 'placeholder' => 'Your password'],
                    'label' => 'Password: '
                ],
                'second_options' => [
                    'attr' => [ 'placeholder' => 'Confirm password'],
                    'label' => 'Repeat Password: '
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [ 'placeholder' => 'Your email address' ],
                'label' => 'Email: '
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'type' => 'submit',
                    'form' => 'register_form',
                    'value' => 'submited'
                ],
                'label' => 'Register!'
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'register_user';
    }


}
