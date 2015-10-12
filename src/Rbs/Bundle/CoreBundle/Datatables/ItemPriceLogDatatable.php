<?php

namespace Rbs\Bundle\CoreBundle\Datatables;

use Rbs\Bundle\CoreBundle\Entity\ItemPriceLog;

/**
 * Class ItemPriceLogDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class ItemPriceLogDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('items_price_log_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('item.name', 'column', array('title' => 'Item Name'))
            ->add('currentPrice', 'column', array('title' => 'Current Price'))
            ->add('previousPrice', 'column', array('title' => 'Previous Price'))
            ->add('updatedAt', 'datetime', array('title' => 'Update Date',
                    'date_format' => 'LLL' ))
            ->add('updatedBy.profile.fullName', 'column', array('title' => 'Update By'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\CoreBundle\Entity\ItemPriceLog';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'item_price_log_datatable';
    }
}
