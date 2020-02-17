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

class VehicleNourishForm extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('depo', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Depo',
                'property' => 'name',
                'required' => true,
                'multiple' => true,
                'empty_value' => 'Select Depo',
                'empty_data' => null,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Depo should not be blank'
                    ))
                ),
                'query_builder' => function (DepoRepository $repository)
                {
                    return $repository->createQueryBuilder('d')
                        ->andWhere('d.deletedAt IS NULL')
                        ->andWhere('d.depotType = :type')
                        ->setParameter('type', Depo::DEPOT_TYPE_CHICK)
                        ;
                }
            ))
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\VehicleNourish'
        ));
    }

    public function getName()
    {
        return 'vehicle_nourish';
    }
}
