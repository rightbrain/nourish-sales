<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\ProjectRepository;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Rbs\Bundle\SalesBundle\Repository\StockRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
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
            ->add($builder->create('created_at', 'text', array(
                'label' => 'Date',
                'attr' => array(
                    'class' => 'date-picker'
                ),
                'empty_data' => new \DateTime(),
                'read_only' => true,
                'required' => false
            ))->addViewTransformer(new DateTimeToStringTransformer(null, null, 'Y-m-d')))
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
