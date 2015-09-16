<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Rbs\Bundle\SalesBundle\Repository\OrderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentOrderForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', 'entity', array(
                'class' => 'RbsSalesBundle:Order',
                'property' => 'id',
                'required' => false,
                'mapped' => false,
                'empty_value' => 'Select Order',
                'empty_data' => null,
//                'query_builder' => function (OrderRepository $repository)
//                {
//                    return $repository->createQueryBuilder('i')
//                        ->where('i.deletedAt IS NULL')
//                        ->orderBy('i.name','ASC')
//                        ->join('i.bundles', 'bundles')
//                        ->andWhere('bundles.id = :saleBundleId')->setParameter('saleBundleId', RbsSalesBundle::ID);
//                }
            ))
            ->add('remove', 'button')
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
        return 'payment_order';
    }
}
