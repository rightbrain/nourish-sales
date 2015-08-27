<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\ProjectRepository;
use Rbs\Bundle\CoreBundle\Repository\WarehouseRepository;
use Rbs\Bundle\SalesBundle\Repository\StockRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class StockHistoryForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity')
            ->add('stock', 'entity', array(
                'class' => 'RbsSalesBundle:Stock',
                'property' => 'item.name',
                'required' => false,
                'empty_value' => 'Select Item',
                'empty_data' => null,
                'query_builder' => function (StockRepository $repository)
                {
                    return $repository->createQueryBuilder('s')
                        ->join('s.item', 'i')
                        ->where('i.deletedAt IS NULL')
                        ->orderBy('i.name','ASC');
                }
            ))
            ->add('fromFactory', 'entity', array(
                'class' => 'RbsCoreBundle:Project',
                'property' => 'projectName',
                'required' => false,
                'empty_value' => 'Select Project',
                'empty_data' => null,
                'query_builder' => function (ProjectRepository $repository)
                {
                    return $repository->createQueryBuilder('p')
                        ->where('p.deletedAt IS NULL')
                        ->orderBy('p.projectName','ASC');
                }
            ))
            ->add('toWarehouse', 'entity', array(
                'class' => 'RbsCoreBundle:Warehouse',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select Warehouse',
                'empty_data' => null,
                'query_builder' => function (WarehouseRepository $repository)
                {
                    return $repository->createQueryBuilder('w')
                        ->where('w.deletedAt IS NULL')
                        ->orderBy('w.name','ASC');
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\StockHistory'
        ));
    }

    public function getName()
    {
        return 'stock';
    }
}
