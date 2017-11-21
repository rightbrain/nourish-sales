<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\AgentNourishBank;

/**
 * Class Agent Nourish Bank Datatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class AgentNourishBankDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var AgentNourishBank $agentNourishBank
         * @return mixed
         */
        $formatter = function($line){
            $agentNourishBank = $this->em->getRepository('RbsSalesBundle:AgentNourishBank')->find($line['id']);
            $line['bankName'] = $agentNourishBank->getAccount()->getBranch()->getBank()->getName();
            $line['fullName'] = $agentNourishBank->getAgent()->getUser()->getProfile()->getFullName();
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

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('agent_nourish_banks_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('fullName', 'virtual', array('title' => 'Agent name'))
            ->add('agent.agentID', 'column', array('title' => 'Agent ID'))
            ->add('account.code', 'column', array('title' => 'Code'))
            ->add('account.name', 'column', array('title' => 'Account'))
            ->add('account.branch.name', 'column', array('title' => 'Branch'))
            ->add('bankName', 'virtual', array('title' => 'Bank'))
//            ->add(null, 'action', array(
//                'width' => '200px',
//                'title' => 'Delete',
//                'start_html' => '<div class="wrapper">',
//                'end_html' => '</div>',
//                'actions' => array(
//                    array(
//                        'route' => 'agent_nourish_banks_delete',
//                        'route_parameters' => array(
//                            'id' => 'id'
//                        ),
//                        'label' => 'Delete',
//                        'icon' => 'glyphicon glyphicon-edit',
//                        'attributes' => array(
//                            'rel' => 'tooltip',
//                            'title' => 'edit-action',
//                            'class' => 'btn btn-primary btn-xs',
//                            'role' => 'button'
//                        ),
//                        'confirm' => false,
//                        'confirm_message' => 'Are you sure?',
//                        'role' => 'ROLE_ADMIN',
//                    )
//                )
//            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\AgentNourishBank';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'agent_nourish_bank_datatable';
    }
}
