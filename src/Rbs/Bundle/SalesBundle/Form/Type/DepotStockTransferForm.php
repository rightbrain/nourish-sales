<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\CoreBundle\Repository\ItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DepotStockTransferForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transferredQuantity','integer',array(
                'required'   => true,
                'attr'=>array('min'=>1)
            ))
            ->add('transferredToDepot', 'entity', array(
                'class' => 'RbsCoreBundle:Depo',
                'property' => 'name',
                'required' => true,
                'empty_value' => 'Select Hatchery',
                'empty_data' => null,
                'query_builder' => function (DepoRepository $repository)
                {
                    return $repository->createQueryBuilder('d')
                        ->where('d.deletedAt IS NULL')
                        ->andWhere('d.depotType =:type')
                        ->setParameter('type', Depo::DEPOT_TYPE_CHICK)
                        ->orderBy('d.name','ASC');
                }
            ))
            ->add('receivedFromDepot', 'entity', array(
                'class' => 'RbsCoreBundle:Depo',
                'property' => 'name',
                'required' => true,
                'empty_value' => 'Select Hatchery',
                'empty_data' => null,
                'query_builder' => function (DepoRepository $repository)
                {
                    return $repository->createQueryBuilder('d')
                        ->where('d.deletedAt IS NULL')
                        ->andWhere('d.depotType =:type')
                        ->setParameter('type', Depo::DEPOT_TYPE_CHICK)
                        ->orderBy('d.name','ASC');
                }
            ))
            ->add('item', 'entity', array(
                'class' => 'RbsCoreBundle:Item',
                'property' => 'name',
                'required' => true,
                'empty_value' => 'Select Item',
                'empty_data' => null,
                'query_builder' => function (ItemRepository $repository)
                {
                    return $repository->createQueryBuilder('i')
                        ->join('i.itemType','it')
                        ->where('i.deletedAt IS NULL')
                        ->andWhere('it.itemType =:type')
                        ->setParameter('type', ItemType::Chick)
                        ->orderBy('i.name','ASC');
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\DailyDepotStock'
        ));
    }

    public function getName()
    {
        return 'depot_stock_transfer';
    }
}
