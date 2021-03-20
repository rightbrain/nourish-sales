<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Repository\ItemRepository;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderItemForm extends AbstractType
{
    /** @var Agent */
    private $agent;

    public function __construct($agent = null)
    {
        $this->agent = $agent;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $itemTypeId = $this->agent && $this->agent->getItemType() ? $this->agent->getItemType()->getId() :  null;
        $itemTypes = $this->agent && $this->agent->getItemTypes() ? $this->agent->getItemTypes() :  null;
        $itemTypesId=array();
        if($itemTypes){
            foreach ($itemTypes as $itemType){
                $itemTypesId[]=$itemType->getId();
            }
        }

        $builder
            ->add('item', 'entity', array(
                'class' => 'RbsCoreBundle:Item',
                'attr' => array(
                    'class' => 'orderItem'
                ),
                'property' => 'getItemCodeName',
                'required' => true,
                'empty_value' => 'Select Item',
                'query_builder' => function (ItemRepository $repository) use ($itemTypeId, $itemTypesId )
                {
                    $qb = $repository->createQueryBuilder('i')
                        ->where('i.deletedAt IS NULL')
                        ->orderBy('i.name','ASC')
                        ->join('i.bundles', 'bundles')
                        ->join('i.itemType', 'itemType')
                        ->where('i.status=1')
                        ->andWhere('itemType.itemType <>:itemType')->setParameter('itemType', ItemType::Chick)
                        ->andWhere('bundles.id = :saleBundleId')->setParameter('saleBundleId', RbsSalesBundle::ID);
                    /*if ($itemTypeId) {
                        $qb->join('i.itemType', 'it');
                        $qb->andWhere($qb->expr()->eq('it.id', $itemTypeId));
                    }*/
                    if ($itemTypesId) {
                        $qb->join('i.itemType', 'it');
                        $qb->andWhere('it.id IN (:itemTypesId)');
                        $qb->setParameter('itemTypesId', $itemTypesId);
                    }

                    return $qb;
                },
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Item should not be blank'
                    )),
                ),
            ))
            ->add('quantity', 'text', array(
                'attr' => array(
                    'class' => 'quantity','autocomplete'=>'off'
                ),
                'empty_data' => 0,
            ))
            ->add('totalAmount', 'text', array(
                'read_only' => true
            ))
            ->add('price', 'text', array(
//                'read_only' => true
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\OrderItem'
        ));
    }

    public function getName()
    {
        return 'order_item';
    }
}
