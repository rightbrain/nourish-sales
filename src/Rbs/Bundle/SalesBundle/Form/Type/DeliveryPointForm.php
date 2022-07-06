<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DeliveryPointForm extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('pointAddress', 'textarea', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Delivery point address should not be blank'
                    ))
                )
            ))
            ->add('contactPerson', 'text', array(
                'required' => false,
                'max_length' => 50,
            ))
            ->add('pointPhone', 'text', array(
                'required' => false,
                'attr' => array('class' => 'input-mask-phone')
            ))
            ->add('status', 'checkbox', array(
                'required' => false,
                'attr' => array('class' => 'form-control')
            ))
            ->add('submit', 'submit', array(
                'attr'     => array('class' => 'btn green')
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\DeliveryPoint'
        ));
    }

    public function getName()
    {
        return 'delivery_point';
    }
}
