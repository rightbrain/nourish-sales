<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\CustomerRepository;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentForm extends AbstractType
{
    /** @var Request */
    private $request;

    public function __construct($request = null)
    {
        $this->request = $request;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customer = $this->request->request->get('payment[customer]', null, true);

        $builder
            ->add('amount', 'text', array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
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
                        ->join('u.profile', 'p')
                        ->where('u.deletedAt IS NULL')
                        ->andWhere('u.enabled = 1')
                        ->andWhere('u.userType = :CUSTOMER')
                        ->setParameter('CUSTOMER', 'CUSTOMER')
                        ->orderBy('p.fullName','ASC');
                }
            ))
            ->add('remark', 'textarea')
        ;

        $builder
            ->add('orders', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'property' => 'orderIdAndDueAmount',
                'multiple' => true,
                'query_builder' => function (OrderRepository $repository) use ($customer)
                {
                    if (!$customer) {
                        return $repository->createQueryBuilder('o')
                            ->setMaxResults(0);
                    } else {
                        return $repository->getCustomerWiseOrder($customer, true);
                    }
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
