<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CashReceiveForm extends AbstractType
{
    private $depoId;

    public function __construct($depoId)
    {
        $this->depoId = $depoId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if($this->depoId != 0){
            $builder
            ->add('orderRef', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'orderIdAndAgent',
                'required' => true,
                'empty_value' => 'Select Order',
                'empty_data' => null,
                'query_builder' => function (OrderRepository $repository)
                {
                    return $repository->createQueryBuilder('o')
                        ->join('o.agent', 'a')
                        ->join('o.deliveries', 'deliveries')
                        ->join('deliveries.depo', 'd')
                        ->where('o.orderState != :CANCEL')
                        ->andWhere('o.orderState != :PROCESSING')
                        ->andWhere('d.id = :depoId')
                        ->setParameters(array('CANCEL'=>Order::ORDER_STATE_CANCEL, 'PROCESSING'=>Order::ORDER_STATE_PROCESSING, 'depoId'=>$this->depoId));
                }
            ));
        }else{
            $builder
            ->add('orderRef', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'orderIdAndAgent',
                'required' => true,
                'empty_value' => 'Select Order',
                'empty_data' => null,
                'query_builder' => function (OrderRepository $repository)
                {
                    return $repository->createQueryBuilder('o')
                        ->join('o.agent', 'a')
                        ->where('o.orderState != :CANCEL')
                        ->andWhere('o.orderState != :PROCESSING')
                        ->setParameters(array('CANCEL'=>Order::ORDER_STATE_CANCEL, 'PROCESSING'=>Order::ORDER_STATE_PROCESSING));
                }
            ));
        }
        $builder
            ->add('amount')
            ->add('depositor')
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\CashReceive'
        ));
    }

    public function getName()
    {
        return 'cash_receive';
    }
}
