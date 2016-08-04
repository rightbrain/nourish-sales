<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class DamageGoodDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class DamageGoodDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('damage_good_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('user.username', 'column', array('title' => 'User'))
            ->add('agent.user.username', 'column', array('title' => 'Agent'))
            ->add('remark', 'column', array('title' => 'Remark'))
            ->add('amount', 'column', array('title' => 'Amount'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\DamageGood';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'damage_good_datatable';
    }
}
