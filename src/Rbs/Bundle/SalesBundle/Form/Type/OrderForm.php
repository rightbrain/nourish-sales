<?php

namespace Rbs\Bundle\SalesBundle\Form\Type;

use Rbs\Bundle\SalesBundle\Repository\CustomerRepository;
use Rbs\Bundle\SalesBundle\Repository\SmsRepository;
use Rbs\Bundle\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customer = $options['data']->getCustomer();
        $refSMS = $options['data']->getRefSms();

        if (!$customer) {
            $builder
                ->add('customer', 'entity', array(
                    'class' => 'RbsSalesBundle:Customer',
                    'property' => 'user.profile.fullName',
                    'required' => false,
                    'empty_value' => 'Select Customer',
                    'empty_data' => null,
                    'query_builder' => function (CustomerRepository $repository)
                    {
                        return $repository->createQueryBuilder('c')
                            ->join('c.user', 'u')
                            ->join('u.profile', 'p')
                            ->where('u.deletedAt IS NULL')
                            ->andWhere('u.enabled = 1')
                            ->andWhere('u.userType = :CUSTOMER')
                            ->setParameter('CUSTOMER', 'CUSTOMER')
                            ->orderBy('p.fullName','ASC');
                    }
                ));
        }

        $builder->add('totalAmount', 'text', array(
                'read_only' => true
            ))
            ->add('refSMS', 'entity', array(
                'class'         => 'RbsSalesBundle:Sms',
                'property'      => 'cellNumber',
                'required'      => false,
                'empty_value'   => 'Select SMS',
                'empty_data'    => null,
                'query_builder' => function (SmsRepository $repository) use ($refSMS)
                {
                    $query = $repository->createQueryBuilder('sms')
                        ->where('sms.deletedAt IS NULL')
                        ->andWhere('sms.status = :UNREAD')
                        ->andWhere('sms.order IS NULL')
                        ->setParameter('UNREAD', 'UNREAD')
                        ->orderBy('sms.id','DESC');
                    if ($refSMS) {
                        $query->orWhere("sms.id = :smsId")->setParameter("smsId", $refSMS->getId());
                    }
                    return $query;
                }
            ))
            ->add('remark')
        ;
        $builder
            ->add('orderItems', 'collection', array(
                'type'         => new OrderItemForm(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label_attr'   => array(
                    'class' => 'hidden',
                ),
            ))
        ;
        $builder
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
            'data_class' => 'Rbs\Bundle\SalesBundle\Entity\Order'
        ));
    }

    public function getName()
    {
        return 'order';
    }
}
