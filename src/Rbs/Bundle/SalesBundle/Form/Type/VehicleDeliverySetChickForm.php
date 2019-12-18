<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Repository\DeliveryRepository;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Rbs\Bundle\SalesBundle\Repository\VehicleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class VehicleDeliverySetChickForm extends AbstractType
{
    private $user;
    private $agent;
    private $vehicleId;

    public function __construct($user, $vehicle)
    {
        /** @var Vehicle $vehicle */
        $this->user = $user;
        $this->vehicleId = $vehicle->getId();
        $this->agent = $vehicle->getAgent()?$vehicle->getAgent():null;
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
                'property' => 'getOrderInfoWithAgent',
                'required' => true,
                'multiple' => true,
                'query_builder' => function (OrderRepository $repository)
                {
                    $qp = $repository->createQueryBuilder('o');
                        $qp->join('o.depo', 'd');
                        $qp->join('d.users', 'u');
                        $qp->where('o.deliveryState = :PARTIALLY_SHIPPED OR o.deliveryState = :READY');
                        $qp->andWhere('u.id = :user');
                        $qp->andWhere('o.vehicleState IS NULL or o.vehicleState = :vehicle');
                        $qp->setParameters(array('PARTIALLY_SHIPPED'=>Order::DELIVERY_STATE_PARTIALLY_SHIPPED, 'READY'=>Order::DELIVERY_STATE_READY,
                            'user' => $this->user->getId()));
                        $qp->setParameter('vehicle', Order::VEHICLE_STATE_PARTIALLY_SHIPPED);
                        if($this->agent){
                            $qp->andWhere('o.agent = :agent');
                            $qp->setParameter('agent', $this->agent);
                        }
                        $qp->orderBy('o.id', 'desc');
                    return $qp;
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
