<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Repository\AgentRepository;
use Rbs\Bundle\SalesBundle\Repository\SmsRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderWithoutSmsForm extends AbstractType
{

    /** @var  EntityManager */
    private $em;

    /** @var Agent */
    private $agent;

    public function __construct($entityManager, $agent=null, $request = null)
    {
        $this->em = $entityManager;
        $this->agent = $agent;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Agent $agentAjax */
        $agentAjax = $options['data']->getAgent();

            if(!$agentAjax){
                $builder
                    ->add('agent', 'entity', array(
                        'class' => 'RbsSalesBundle:Agent',
                        'attr' => array(
                            'class' => 'select2me'
                        ),
                        'property' => 'getIdName',
                        'required' => true,
                        'empty_value' => 'Select Agent',
                        'query_builder' => function (AgentRepository $repository)
                        {
                            $query = $repository->createQueryBuilder('c')
                                ->join('c.user', 'u')
                                ->join('u.profile', 'p')
                                ->where('u.deletedAt IS NULL')
                                ->andWhere('u.enabled = 1')
                                ->andWhere('u.userType = :AGENT')
                                ->setParameter('AGENT', User::AGENT)
                                ->orderBy('p.fullName','ASC');
                            if($this->agent){
                                $query->andWhere('c.id = :agentId');
                                $query->setParameter('agentId', $this->agent);
                            }

                            return $query;
                        },
                        'constraints' => array(
                            new NotBlank(array(
                                'message'=>'Agent should not be blank'
                            )),
                        ),
                        'data'=>($this->agent)?$this->em->getReference("RbsSalesBundle:Agent",$this->agent):null
                    ));
            }
               $builder ->add('depo', 'entity', array(
                    'class' => 'Rbs\Bundle\CoreBundle\Entity\Depo',
                    'attr' => array(
                        'class' => 'select2me'
                    ),
                    'property' => 'name',
                    'required' => false,
                    'empty_value' => 'Select Depo',
                    'empty_data' => null,
                    'query_builder' => function (DepoRepository $repository)
                    {
                        return $repository->createQueryBuilder('p')
                            ->andWhere('p.deletedAt IS NULL')
                            ;
                    }
                ))
            ;

        $builder
            ->add('totalAmount', 'text', array(
                'read_only' => true
            ));


        $builder
            ->add('remark');
        $builder
            ->add('orderItems', 'collection', array(
                'type'         => new OrderItemForm($agentAjax),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label_attr'   => array(
                    'class' => 'hidden',
                ),
            ))
            ->add('payments', 'collection', array(
                'type'         => new PaymentWithoutSmsForm($agentAjax, $this->em),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label_attr'   => array(
                    'class' => 'hidden',
                ),
            ))
        ;
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Order'
        ));
    }

    public function getName()
    {
        return 'order';
    }
}
