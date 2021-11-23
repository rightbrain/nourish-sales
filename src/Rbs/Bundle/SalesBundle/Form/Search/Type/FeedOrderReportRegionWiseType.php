<?php

namespace Rbs\Bundle\SalesBundle\Form\Search\Type;

use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\CoreBundle\Repository\LocationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedOrderReportRegionWiseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('region', 'entity', array(
                'class' => 'RbsCoreBundle:Location',
                'attr' => array(
                    'placeholder' => 'Select Region',
                    'class' => 'select2me input-medium'
                ),
                'property' => 'name',
                'required' => true,
                'multiple' => true,
                'empty_value' => 'Select Region',
                'empty_data' => null,
                'query_builder' => function (LocationRepository $repository)
                {
                    return $repository->createQueryBuilder('l')
                        ->where('l.level = 3')
                        ->orderBy('l.name','ASC');
                }
            ))
            ->add('start_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker input-small form-control',
                    'autocomplete'=>'off'
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
        return 'feed_order_report_region_wise';
    }
}
