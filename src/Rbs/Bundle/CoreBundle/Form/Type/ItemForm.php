<?php

namespace Rbs\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', 'choice', array(
                'choices' => array('Disable', 'Enable'),
                'attr' => array(
                    'class' => 'input-small'
                )
            ))
            ->add('name')
            ->add('sku', null, array(
                'label' => 'Item Code'
            ))
            ->add('itemUnit')
            ->add('price')
            ->add('itemType', null, array(
                'attr' => array('class' => 'select2me')
            ))
            ->add('category')
            ->add('bundles', null, array(
                'label' => 'Modules'
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rbs\Bundle\CoreBundle\Entity\Item'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rbs_bundle_corebundle_item';
    }
}
