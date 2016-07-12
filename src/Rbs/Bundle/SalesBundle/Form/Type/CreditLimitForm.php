<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\CategoryRepository;
use Rbs\Bundle\SalesBundle\Repository\AgentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditLimitForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', 'text')
            ->add('startDate', 'date', array(
                'widget' => 'single_text',
                'html5' => false,
                'attr' => array(
                    'class' => 'date-picker'
                )
            ))
            ->add('endDate', 'date', array(
                'widget' => 'single_text',
                'html5' => false,
                'attr' => array(
                    'class' => 'date-picker'
                )
            ))
            ->add('agent', 'entity', array(
                'class' => 'RbsSalesBundle:Agent',
                'property' => 'user.profile.fullName',
                'required' => false,
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
            ->add('category', 'entity', array(
                'class' => 'RbsCoreBundle:Category',
                'property' => 'categoryName',
                'required' => false,
                'empty_value' => 'Select Category',
                'empty_data' => null,
                'query_builder' => function (CategoryRepository $repository)
                {
                    return $repository->createQueryBuilder('c')
                        ->where('c.deletedAt IS NULL');
                }
            ))
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\CreditLimit'
        ));
    }

    public function getName()
    {
        return 'credit_limit';
    }
}
