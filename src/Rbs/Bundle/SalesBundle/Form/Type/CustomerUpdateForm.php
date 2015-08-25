<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\AreaRepository;
use Rbs\Bundle\CoreBundle\Repository\WarehouseRepository;
use Rbs\Bundle\SalesBundle\Repository\CustomerGroupRepository;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUpdateForm extends AbstractType
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
            ->add('customerID')
            ->add('creditLimit')
            ->add('balance')
            ->add('agent', 'entity', array(
                'class' => 'RbsUserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select Agent',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->where('u.userType = :Agent')
                        ->setParameter('Agent', 'Agent')
                        ->orderBy('u.username','ASC');
                }
            ))
            ->add('warehouse', 'entity', array(
                'class' => 'RbsCoreBundle:Warehouse',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select Warehouse',
                'empty_data' => null,
                'query_builder' => function (WarehouseRepository $repository)
                {
                    return $repository->createQueryBuilder('w')
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
                        ->orderBy('a.areaName','ASC');
                }
            ))
            ->add('customerGroup', 'entity', array(
                'class' => 'RbsSalesBundle:CustomerGroup',
                'property' => 'label',
                'required' => false,
                'empty_value' => 'Select Group',
                'empty_data' => null,
                'query_builder' => function (CustomerGroupRepository $repository)
                {
                    return $repository->createQueryBuilder('cg')
                        ->orderBy('cg.label','ASC');
                }
            ))
        ;

        $builder
            ->add('user', new UserCustomerUpdateForm());

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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Customer'
        ));
    }

    public function getName()
    {
        return 'user_customer';
    }
}
