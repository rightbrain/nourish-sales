<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\CustomerRepository;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', 'text')
            ->add('bankName', 'text')
            ->add('branchName', 'text')
            ->add('paymentMethod', 'choice', array(
                'empty_value' => 'Select Payment Method',
                'choices'  => array(
                    'BANK' => 'BANK',
                    'CHEQUE' => 'CHEQUE',
                    'CACHE' => 'CACHE'
                ),
                'required' => false,
            ))
            ->add('customer', 'entity', array(
                'class' => 'RbsSalesBundle:Customer',
                'property' => 'user.username',
                'required' => false,
                'empty_value' => 'Select Customer',
                'empty_data' => null,
                'query_builder' => function (CustomerRepository $repository)
                {
                    return $repository->createQueryBuilder('c')
                        ->join('c.user', 'u')
                        ->where('u.deletedAt IS NULL')
                        ->andWhere('u.enabled = 1')
                        ->andWhere('u.userType = :CUSTOMER')
                        ->setParameter('CUSTOMER', 'CUSTOMER')
                        ->orderBy('u.username','ASC');
                }
            ))
            ->add('remark', 'textarea')
        ;

        $builder
                ->add('orders', 'entity', array(
                    'class' => 'RbsSalesBundle:Order',
                    'property' => 'id',
                    'multiple' => true,
                    'query_builder' => function (OrderRepository $repository)
                    {
                        return $repository->createQueryBuilder('o')
                            ->where('o.deletedAt IS NULL')
                            ->orderBy('o.id','DESC')
                            ->andWhere('o.orderState != :complete or o.orderState != :cancel')
                            ->andWhere('o.paymentState != :paid')
                            ->setParameter('complete', Order::ORDER_STATE_COMPLETE)
                            ->setParameter('cancel', Order::ORDER_STATE_CANCEL)
                            ->setParameter('paid', Order::PAYMENT_STATE_PAID);
                    }
                ));
        $builder
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Payment'
        ));
    }

    public function getName()
    {
        return 'payment';
    }
}
