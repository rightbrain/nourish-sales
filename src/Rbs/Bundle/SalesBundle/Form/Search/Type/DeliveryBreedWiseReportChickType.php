<?php

namespace Rbs\Bundle\SalesBundle\Form\Search\Type;

use Doctrine\ORM\EntityRepository;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\CoreBundle\Repository\ItemRepository;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryBreedWiseReportChickType extends AbstractType
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
                        ->where('d.deletedAt IS NULL')
                        ->andWhere('d.depotType =:type')
                        ->setParameter('type', Depo::DEPOT_TYPE_CHICK)
                        ;

                }
            ))
            ->add('item', 'entity', array(
                'class' => 'RbsCoreBundle:Item',
                'attr' => array(
                    'class' => 'orderItem'
                ),
                'property' => 'getItemCodeName',
                'required' => true,
                'empty_value' => 'Select Item',
                'query_builder' => function (ItemRepository $repository)
                {
                    $qb = $repository->createQueryBuilder('i')
                        ->where('i.deletedAt IS NULL')
                        ->orderBy('i.name','ASC')
                        ->join('i.bundles', 'bundles')
                        ->join('i.itemType', 'itemType')
                        ->where('i.status=1')
                        ->andWhere('itemType.itemType =:itemType')->setParameter('itemType', ItemType::Chick)
                        ->andWhere('bundles.id = :saleBundleId')->setParameter('saleBundleId', RbsSalesBundle::ID);
                    return $qb;
                }
            ))
            ->add('start_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker input-small form-control',
                    'autocomplete'=>'off',
                    'placeholder' => 'From Date',
                )
            ))
            ->add('end_date', 'text', array(
                'attr' => array(
                    'class' => 'date-picker input-small form-control',
                    'autocomplete'=>'off',
                    'placeholder' => 'To Date',
                )
            ))
            ->add('zilla', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Location',
                'attr' => array(
                    'placeholder' => 'Select District',
                ),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.level = :level')->setParameter('level', 4)->orderBy('a.name');
                },
                'required' => false,
                'empty_value' => 'Select District',
                'empty_data' => null,
                'multiple' => false,
            ))
            ->add('region', 'entity', array(
                'class' => 'Rbs\Bundle\CoreBundle\Entity\Location',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.level = :level')->setParameter('level', 3)->orderBy('a.name');
                },
                'required' => false,
                'empty_value' => 'Select Region',
                'empty_data' => null,
                'multiple' => false,
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
