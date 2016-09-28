<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DamageGoodForm extends AbstractType
{
    private $depo;

    public function __construct($depo)
    {
        $this->depo = $depo;
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
                    return  $repository->createQueryBuilder('o')
                        ->join('o.depo', 'd')
                        ->where('o.orderState = :COMPLETE')
                        ->andWhere('d.id = :depo')
                        ->setParameter('depo', $this->depo)
                        ->setParameter('COMPLETE', Order::ORDER_STATE_COMPLETE);
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
