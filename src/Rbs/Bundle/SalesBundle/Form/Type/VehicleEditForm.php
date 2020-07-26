<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class VehicleEditForm extends AbstractType
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('driverName', 'text', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Driver name should not be blank'
                    ))
                )
            ))
            ->add('driverPhone', 'text', array(
                'required' => true,
                'max_length' => 50,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Phone number should not be blank'
                    )),
                    new Regex(array(
                        'pattern'   => '/^(\+?\(?\d{2,4}\)?[\d\s-]{3,})$/',
                        'match'     => true,
                        'message' =>'Wrong phone number'
                    ))
                )
            ))
            ->add('truckNumber', 'text', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Truck number should not be blank'
                    ))
                )
            ))
            ->add('remark')
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Vehicle'
        ));
    }

    public function getName()
    {
        return 'vehicle';
    }
}
