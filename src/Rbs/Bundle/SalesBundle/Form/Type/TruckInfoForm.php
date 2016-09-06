<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TruckInfoForm extends AbstractType
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
        if($this->user->getUserType() != User::AGENT){
            $builder
                ->add('orders', 'entity', array(
                    'class' => 'RbsSalesBundle:Order',
                    'property' => 'getOrderIdAndAgent',
                    'required' => false,
                    'multiple' => true,
                    'empty_value' => 'Select Order',
                    'empty_data' => null,
                    'query_builder' => function (OrderRepository $repository)
                    {
                        return $repository->createQueryBuilder('o')
                            ->where('o.deliveryState != :COMPLETE')
                            ->andWhere('o.orderState != :CANCEL')
                            ->setParameters(array('COMPLETE'=>Order::DELIVERY_STATE_SHIPPED, 'CANCEL'=>Order::ORDER_STATE_CANCEL));
                    }
                ));
        }else{
            $builder
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
                        ->where('u.id =:user')
                        ->andWhere('o.deliveryState != :COMPLETE')
                        ->andWhere('o.orderState != :CANCEL')
                        ->setParameters(array('COMPLETE'=>Order::DELIVERY_STATE_SHIPPED, 'CANCEL'=>Order::ORDER_STATE_CANCEL,
                            'user'=>$this->user->getId() ));
                }
            ));
        }
        $builder
            ->add('driverName', 'text', array(
                'required' => true,
            ))
            ->add('driverPhone', 'text', array(
                'required' => true,
            ))
            ->add('truckNumber', 'text', array(
                'required' => true,
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\TruckInfo'
        ));
    }

    public function getName()
    {
        return 'truck_info';
    }
}
