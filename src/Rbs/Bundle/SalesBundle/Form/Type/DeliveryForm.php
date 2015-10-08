<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\WarehouseRepository;
use Rbs\Bundle\SalesBundle\Repository\CustomerRepository;
use Rbs\Bundle\SalesBundle\Repository\SmsRepository;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeliveryForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contactName')
            ->add('contactNo')
            ->add('otherInfo')
            ->add('warehouse', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Warehouse',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select Warehouse',
                'empty_data' => null,
                'query_builder' => function (WarehouseRepository $repository)
                {
                    return $repository->createQueryBuilder('p')
                        ->andWhere('p.deletedAt IS NULL')
                        ;
                }
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Delivery'
        ));
    }

    public function getName()
    {
        return 'delivery';
    }
}
