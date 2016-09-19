<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Rbs\Bundle\SalesBundle\Repository\VehicleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleDeliverySetForm extends AbstractType
{
    private $user;
    private $vehicleId;

    public function __construct($user, $vehicleId)
    {
        $this->user = $user;
        $this->vehicleId = $vehicleId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vehicle', 'entity', array(
                'class' => 'RbsSalesBundle:Vehicle',
                'property' => 'truckInformation',
                'query_builder' => function (VehicleRepository $repository)
                {
                    return $repository->createQueryBuilder('v')
                        ->where('v.id = :vehicleId')
                        ->setParameter('vehicleId', $this->vehicleId);
                }
            ))
            ->add('orders', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'property' => 'id',
                'required' => true,
                'multiple' => true,
                'empty_value' => 'Select Order',
                'empty_data' => null,
                'query_builder' => function (OrderRepository $repository)
                {
                    return $repository->createQueryBuilder('o')
                        ->join('o.agent', 'a')
                        ->join('a.user', 'u')
                        ->where('o.deliveryState != :COMPLETE')
                        ->andWhere('o.orderState != :CANCEL')
                        ->andWhere('o.orderState != :PENDING')
                        ->setParameters(array('COMPLETE'=>Order::DELIVERY_STATE_SHIPPED, 'CANCEL'=>Order::ORDER_STATE_CANCEL,
                            'PENDING'=>Order::ORDER_STATE_PENDING));
                }
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

        ));
    }

    public function getName()
    {
        return 'vehicle_delivery_form';
    }
}
