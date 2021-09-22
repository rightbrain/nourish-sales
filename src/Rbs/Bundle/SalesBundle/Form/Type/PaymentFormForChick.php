<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Rbs\Bundle\CoreBundle\Repository\BankBranchRepository;
use Rbs\Bundle\CoreBundle\Repository\BankRepository;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\AgentBank;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Repository\AgentBankRepository;
use Rbs\Bundle\SalesBundle\Repository\AgentRepository;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Rbs\Bundle\CoreBundle\Form\Transformer\BankAccountTransformer;

class PaymentFormForChick extends AbstractType
{
    /** @var Request */
    private $request;

    /** @var  EntityManager */
    private $em;

    public function __construct($entityManager, $request = null)
    {
        $this->em = $entityManager;
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
            ->add('bankAccount', 'choice', array(
                'required' => false,
                'choices' => $this->getAccountList(),
                'attr' => array('class' => 'select2me')
            ))
            ->add('depositDate', 'datetime', array(
                'widget'=>'single_text',
                'format' => 'yyyy-MM-dd',
                'html5'=> false,
                'attr' => array(
                    'class' => 'form-control input-medium date-month-year-picker'
                )
            ))
            ->add('paymentMethod', 'choice', array(
                'choices'  => array(
                    'BANK' => 'BANK',
                    'CHEQUE' => 'CHEQUE',
                    'CASH' => 'CASH'
                ),
                'empty_data' => null,
                'required' => false,
                'placeholder' => null
            ))
            ->add('fxCx', 'choice', array(
                'choices'  => array(
                    'CK' => 'CHICK'
                ),
                'empty_data' => null,
                'required' => false,
                'placeholder' => null
            ))
            ->add('agent', 'entity', array(
                'class' => 'RbsSalesBundle:Agent',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'getIdName',
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
                        ->andWhere('c.agentType = :type')
                        ->setParameter('type', Agent::AGENT_TYPE_CHICK)
                        ->orderBy('p.fullName','ASC');
                }
            ))
            ->add('agentBankBranch', 'entity', array(
                'class' => 'RbsSalesBundle:AgentBank',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'getBankBranchName',
                'empty_value' => 'Select Agent Bank',
                'empty_data' => null,
                'query_builder' => function (AgentBankRepository $repository)
                {
                    return $repository->createQueryBuilder('ab')
                        ->where('ab.deletedAt IS NULL');
                }
            ))
            ->add('bank', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Bank',
                'required' => true,
                'constraints' => array(
                    new NotBlank(array('message' => 'Bank should not be blank')),
                ),
                'attr' => array(
                    'class' => ''
                ),
                'property' => 'name',
                'empty_value' => 'Select Bank',
                'empty_data' => null,
                'query_builder' => function (BankRepository $repository)
                {
                    $qb = $repository->createQueryBuilder('bank')
                        ->where('bank.name IS NOT NULL');
                    return $qb;
                }
            ))

            ->add('branch', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\BankBranch',
                'required' => true,
                'constraints' => array(
                    new NotBlank(array('message' => 'Branch should not be blank')),
                ),
                'attr' => array(
                    'class' => ''
                ),
                'property' => 'name',
                'empty_value' => 'Select Branch',
                'empty_data' => null,
                'query_builder' => function (BankBranchRepository $repository)
                {
                    $qb = $repository->createQueryBuilder('branch')
                        ->where('branch.name IS NOT NULL');
                    return $qb;
                }
            ))
            ->add('remark', 'textarea', array(
                'required' => false,
            ))
        ;

//        $builder
//            ->add('orders', 'entity', array(
//                'class' => 'RbsSalesBundle:Order',
//                'property' => 'orderIdAndDueAmount',
//                'multiple' => true,
//                'required' => false,
//                'query_builder' => function (OrderRepository $repository) use ($agent)
//                {
//                    if (!$agent) {
//                        return $repository->createQueryBuilder('o')
//                            ->setMaxResults(0);
//                    } else {
//                        return $repository->getAgentWiseOrder($agent, true);
//                    }
//                }
//            ));

        $builder
            ->add('submit', 'submit', array(
                'attr'     => array('class' => 'btn green')
            ))
        ;

        $builder->get('bankAccount')
            ->addModelTransformer(new BankAccountTransformer($this->em));
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

    private function getAccountList()
    {
        return $this->em->getRepository('RbsCoreBundle:BankAccount')->getAccountListWithBankBranch();
    }
}
