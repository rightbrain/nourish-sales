<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Repository\DeliveryRepository;
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
            ->add('deliveries', 'entity', array(
                'class' => 'RbsSalesBundle:Delivery',
                'property' => 'deliveryInfo',
                'required' => true,
                'multiple' => false,
                'empty_value' => 'Select Delivery',
                'empty_data' => null,
                'query_builder' => function (DeliveryRepository $repository)
                {
                    return $repository->createQueryBuilder('sales_deliveries')
                        ->join('sales_deliveries.depo', 'd')
                        ->join('sales_deliveries.orders', 'o')
                        ->join('d.users', 'u')
                        ->andWhere('u =:user')
                        ->andWhere('o.deliveryState IN (:READY) OR o.deliveryState IN (:PARTIALLY_SHIPPED)')
                        ->setParameters(array('user'=>$this->user, 'READY'=>Order::DELIVERY_STATE_READY,
                            'PARTIALLY_SHIPPED'=>Order::DELIVERY_STATE_PARTIALLY_SHIPPED));

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
