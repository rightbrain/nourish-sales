<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class AchievementDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class AchievementDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('achievement_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('agent.agentID', 'column', array('title' => 'Agent Name'))
            ->add('quantity', 'column', array('title' => 'Quantity'))
            ->add('user.profile.fullName', 'column', array('title' => 'User Name'))
            ->add('user.userType', 'column', array('title' => 'User Type'))
            ->add('subCategory.subCategoryName', 'column', array('title' => 'Sub Category Name'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Achievement';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'achievement_datatable';
    }
}
