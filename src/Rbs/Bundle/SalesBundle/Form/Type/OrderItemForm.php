<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\ItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderItemForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', 'entity', array(
                'class' => 'RbsCoreBundle:Item',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select Item',
                'empty_data' => null,
                'query_builder' => function (ItemRepository $repository)
                {
                    return $repository->createQueryBuilder('i')
                        ->where('i.deletedAt IS NULL')
                        ->orderBy('i.name','ASC');
                }
            ))
            ->add('quantity', 'text', array(

            ))
            ->add('price', 'text', array(

            ))
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
