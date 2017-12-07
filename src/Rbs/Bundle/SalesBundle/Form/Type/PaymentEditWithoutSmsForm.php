<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityManager;
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

class PaymentEditWithoutSmsForm extends AbstractType
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
            ->add('paymentMethod', 'choice', array(
                'empty_value' => 'Select Payment Method',
                'choices'  => array(
                    'BANK' => 'BANK',
                    'CHEQUE' => 'CHEQUE',
                    'CASH' => 'CASH'
                ),
                'required' => false,
            ))
            ->add('fxCx', 'choice', array(
                'empty_value' => 'Select Payment For',
                'choices'  => array(
                    'FD' => 'FEED',
                    'CK' => 'CHICK'
                ),
                'required' => false,
            ))
            ->add('paymentMode', 'choice', array(
                'empty_value' => 'Select',
                'choices'  => array(
                    'OP' => 'OP',
                    'HO' => 'HO'
                ),
                'required' => false,
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
            ->add('remove', 'button')
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
