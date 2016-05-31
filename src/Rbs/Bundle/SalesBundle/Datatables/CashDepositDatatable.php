<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class CashDepositDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class CashDepositDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        $formatter = function($line){
            $line["monthDiff"] = $this->monthDifference($line['startDate'], $line['endDate']);
            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('cash_deposit_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('user.username', 'column', array('title' => 'Username'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\CashDeposit';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cash_deposit_datatable';
    }

    public function monthDifference($start, $end)
    {
        $difference = $start->diff($end);

        return $difference->m + 1 ;
    }
}
