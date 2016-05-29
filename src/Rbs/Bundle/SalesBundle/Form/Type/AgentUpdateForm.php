<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\AreaRepository;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Repository\AgentGroupRepository;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentUpdateForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('VIP', 'choice', array(
                'choices'  => array(
                    '0' => 'Not',
                    '1' => 'Yes'
                )
            ))
            ->add('agentID')
            ->add('creditLimit')
            ->add('openingBalance')
            ->add('agent', 'entity', array(
                'class' => 'RbsUserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select Agent',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->where('u.userType = :sr')
                        ->andWhere('u.deletedAt IS NULL')
                        ->setParameter('sr', 'SR')
                        ->orderBy('u.username','ASC');
                }
            ))
            ->add('depo', 'entity', array(
                'class' => 'RbsCoreBundle:Depo',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select Depo',
                'empty_data' => null,
                'query_builder' => function (DepoRepository $repository)
                {
                    return $repository->createQueryBuilder('w')
                        ->where('w.deletedAt IS NULL')
                        ->orderBy('w.name','ASC');
                }
            ))
            ->add('area', 'entity', array(
                'class' => 'RbsCoreBundle:Area',
                'property' => 'areaName',
                'required' => false,
                'empty_value' => 'Select Area',
                'empty_data' => null,
                'query_builder' => function (AreaRepository $repository)
                {
                    return $repository->createQueryBuilder('a')
                        ->where('a.deletedAt IS NULL')
                        ->orderBy('a.areaName','ASC');
                }
            ))
            ->add('agentGroup', 'entity', array(
                'class' => 'RbsSalesBundle:AgentGroup',
                'property' => 'label',
                'required' => false,
                'empty_value' => 'Select Group',
                'empty_data' => null,
                'query_builder' => function (AgentGroupRepository $repository)
                {
                    return $repository->createQueryBuilder('cg')
                        ->where('cg.deletedAt IS NULL')
                        ->orderBy('cg.label','ASC');
                }
            ))
        ;

        $builder
            ->add('user', new UserAgentUpdateForm());

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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Agent'
        ));
    }

    public function getName()
    {
        return 'user_agent';
    }
}
