<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\CoreBundle\Repository\ProjectRepository;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TargetSubForm extends AbstractType
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
            ->add('startDate', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message'=>'Name should not be blank'))
                ),
                'label' => 'Start Date',
                'attr' => array(
                    'class' => 'form-control month-year-picker'
                )
            ))
            ->add('endDate', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message'=>'Name should not be blank'))
                ),
                'label' => 'End Date',
                'attr' => array(
                    'class' => 'form-control month-year-picker'
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Target'
        ));
    }

    public function getName()
    {
        return 'target_sub_form';
    }
}
