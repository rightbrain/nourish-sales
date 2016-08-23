<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CashReceiveForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount')
            ->add('order', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'attr' => array(
                    'class' => 'select2me'
                ),
                'property' => 'id',
                'required' => false,
                'empty_value' => 'Select Order',
                'empty_data' => null,
                'query_builder' => function (OrderRepository $repository)
                {
                    return $repository->createQueryBuilder('o')
                        ->join('o.agent', 'a');
                }
            ))
            ->add('depositor')
            ->add('remark')
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\CashReceive'
        ));
    }

    public function getName()
    {
        return 'cash_receive';
    }
}
