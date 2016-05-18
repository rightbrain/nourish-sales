<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class TargetDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class TargetDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Order $order */
        $formatter = function($line){
            //$order = $this->em->getRepository('RbsSalesBundle:Order')->find($line['id']);
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
            'url' => $this->router->generate('target_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('user.username', 'column', array('title' => 'Username'))
            ->add('user.userType', 'column', array('title' => 'Designation'))
            ->add('quantity', 'column', array('title' => 'Quantity'))
            ->add('remaining', 'column', array('title' => 'Remaining'))
            ->add('subCategory.subCategoryName', 'column', array('title' => 'subCategory'))
            ->add('startDate', 'datetime', array('title' => 'startDate', 'date_format' => $dateFormat))
            ->add('endDate', 'datetime', array('title' => 'endDate', 'date_format' => $dateFormat))
            ->add('monthDiff', 'virtual', array('title' => 'Month'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Target';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'target_datatable';
    }

    public function monthDifference($start, $end)
    {
        $difference = $start->diff($end); // $difference->y // $difference->m // $difference->d

        return $difference->m;
    }
}
