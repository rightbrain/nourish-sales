<?php

namespace Rbs\Bundle\SalesBundle\Form\Search\Type;

use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('depo', 'entity', array(
                'class' => 'RbsCoreBundle:Depo',
                'attr' => array(
                    'placeholder' => 'Select Depo',
                    'class' => 'select2me input-medium'
                ),
                'property' => 'name',
                'required' => true,
                'empty_data' => null,
                'query_builder' => function (DepoRepository $repository)
                {
                    return $repository->createQueryBuilder('d')
                        ->where('d.deletedAt IS NULL');
                }
            ))
            ->add('start_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker input-small form-control'
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
        return 'delivery_report';
    }
}
