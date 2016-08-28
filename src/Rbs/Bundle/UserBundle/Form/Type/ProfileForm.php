<?php

namespace Rbs\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', 'text', array(
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'FullName should not be blank'
                    )),
                ),
            ))
            ->add('cellphone', 'text', array(
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Cellphone should not be blank'
                    )),
                ),
            ))
            ->add('designation')
            ->add('address', 'text', array(
                'required' => false
            ))
            ->add('file')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rbs\Bundle\UserBundle\Entity\Profile'
        ));
    }

    public function getName()
    {
        return 'user_profile';
    }
}
