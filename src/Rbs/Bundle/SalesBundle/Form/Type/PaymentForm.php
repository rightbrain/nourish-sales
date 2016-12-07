<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\AgentRepository;
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
        $agent = $this->request->request->get('payment[agent]', null, true);

        $builder
            ->add('amount', null, array(
                'attr' => array(
                    'class' => 'input-small input-mask-amount'
                )
            ))
            ->add('bankName', 'text', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Bank Name should not be blank'
                    )),
                )
            ))
            ->add('branchName', 'text', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Branch Name should not be blank'
                    )),
                )
            ))
            ->add('paymentMethod', 'choice', array(
                'empty_value' => 'Select Payment Method',
                'choices'  => array(
                    'BANK' => 'BANK',
                    'CHEQUE' => 'CHEQUE',
                    'CASH' => 'CASH'
                ),
                'required' => false,
            ))
            ->add('agent', 'entity', array(
                'class' => 'RbsSalesBundle:Agent',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'user.profile.fullName',
                'empty_value' => 'Select Agent',
                'empty_data' => null,
                'query_builder' => function (AgentRepository $repository)
                {
                    return $repository->createQueryBuilder('c')
                        ->join('c.user', 'u')
                        ->join('u.profile', 'p')
                        ->where('u.deletedAt IS NULL')
                        ->andWhere('u.enabled = 1')
                        ->andWhere('u.userType = :AGENT')
                        ->setParameter('AGENT', 'AGENT')
                        ->orderBy('p.fullName','ASC');
                }
            ))
            ->add('remark', 'textarea', array(
                'required' => false,
            ))
        ;

        $builder
            ->add('orders', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'property' => 'orderIdAndDueAmount',
                'multiple' => true,
                'required' => false,
                'query_builder' => function (OrderRepository $repository) use ($agent)
                {
                    if (!$agent) {
                        return $repository->createQueryBuilder('o')
                            ->setMaxResults(0);
                    } else {
                        return $repository->getAgentWiseOrder($agent, true);
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
