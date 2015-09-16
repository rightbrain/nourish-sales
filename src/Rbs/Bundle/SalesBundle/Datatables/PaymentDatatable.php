<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Payment;

/**
 * Class PaymentDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class PaymentDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('payment_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('createdAt', 'datetime', array(
                'title' => 'Date',
                'date_format' => 'LLL' ))
            ->add('customer.user.username', 'column', array('title' => 'Customer'))
            ->add('paymentMethod', 'column', array('title' => 'Payment Method'))
            ->add('amount', 'column', array('title' => 'Amount'))
//            ->add('order.id', 'column', array('title' => 'Order'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Payment';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'payment_datatable';
    }
}
