<?php

namespace Rbs\Bundle\SalesBundle\Form\Search\Type;

use Rbs\Bundle\SalesBundle\Repository\AgentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('agent', 'entity', array(
                'class' => 'RbsSalesBundle:Agent',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'user.profile.fullName',
                'required' => false,
                'empty_value' => 'Select Agent',
                'empty_data' => null,
                'mapped' => false,
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
            ->add('start_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker'
                )
            ))
            ->add('end_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker'
                )
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

        ));
    }

    public function getName()
    {
        return 'search';
    }
}
