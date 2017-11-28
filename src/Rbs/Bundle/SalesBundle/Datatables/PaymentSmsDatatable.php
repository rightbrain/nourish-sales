<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class PaymentSmsDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class PaymentSmsDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions(array_merge($this->defaultOptions(), array('order' => [[0, 'desc']])));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('payment_sms_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                    'title' => 'Date',
                    'date_format' => 'LLL' ))
            ->add('mobileNo', 'column', array('title' => 'Mobile'))
            ->add('msg', 'column', array('title' => 'SMS Text'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Sms';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'payment_sms_datatable';
    }
}
