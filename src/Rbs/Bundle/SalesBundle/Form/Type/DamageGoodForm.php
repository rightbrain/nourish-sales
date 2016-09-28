<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DamageGoodForm extends AbstractType
{
    private $agents;

    public function __construct($agents)
    {
        $this->agents = $agents;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount')
            ->add('remark')
            ->add('orderRef', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'orderInfoForDamageGoods',
                'required' => true,
                'empty_value' => 'Select Order',
                'empty_data' => null,
                'query_builder' => function (OrderRepository $repository)
                {
                    $query =  $repository->createQueryBuilder('o');
                    $query->join('o.agent', 'a');
                    $query->where('o.orderState = :COMPLETE');
                    foreach ($this->agents as $agent){
                        $query->andWhere('o.agent = :agent');
                        $query->setParameter('agent', $agent);
                    }
                    $query->setParameter('COMPLETE', Order::ORDER_STATE_COMPLETE);

                    return $query;
                }
            ))
            ->add('file')
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\DamageGood'
        ));
    }

    public function getName()
    {
        return 'damage_good';
    }
}
