<?php

namespace Rbs\Bundle\SalesBundle\Datatables;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;

/**
 * Class VehicleDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class VehicleDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Vehicle $vehicle
         * @return mixed
         */
        $formatter = function($line){
            $vehicle = $this->em->getRepository('RbsSalesBundle:Vehicle')->find($line['id']);

            if($vehicle->getAgent() != null){
                $agent = $this->em->getRepository('RbsSalesBundle:Agent')->findOneBy(array('id' => $vehicle->getAgent()->getId()));
                $profile = $this->em->getRepository('RbsUserBundle:Profile')->findOneBy(array('user' => $agent->getUser()->getId()));
                $line["fullName"] = $profile->getFullName();
            }else{
                $line["fullName"] = 'NOURISH';
            }

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
        $this->options->setOptions(array_merge($this->defaultOptions(), array('order' => [[0, 'desc']])));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('truck_info_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat));
        $this->columnBuilder
            ->add('agent.user.id', 'column', array('title' => 'Agent/Nourish', 'render' => 'resolveAgentName'));
        $this->columnBuilder
            ->add('driverName', 'column', array('title' => 'Driver Name'))
            ->add('driverPhone', 'column', array('title' => 'Driver Phone'))
            ->add('truckNumber', 'column', array('title' => 'Truck Number'))
            ->add('status', 'column', array('title' => 'Status'))
            ->add('smsText', 'column', array('title' => 'SMS Text'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Vehicle';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'truck_info_datatable';
    }
}
