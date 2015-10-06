<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\ProjectRepository;
use Rbs\Bundle\CoreBundle\Repository\WarehouseRepository;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
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
            ->add('quantity', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message'=>'Name should not be blank'))
                )
            ))
            ->add('stockID', 'hidden', array(
                'mapped' => false
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
                        ->orderBy('p.projectName','ASC')
                        ->join('p.bundles', 'bundles')
                        ->andWhere('bundles.id = :salesBundleId')->setParameter('salesBundleId', RbsSalesBundle::ID);
                }
            ))
            ->add('description')
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
