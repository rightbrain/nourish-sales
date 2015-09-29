<?php
namespace Rbs\Bundle\CoreBundle\EventListener;

use Knp\Menu\MenuItem;
use Rbs\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener extends ContextAwareListener
{
    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigureMain(ConfigureMenuEvent $event)
    {
        /** @var MenuItem $menu */
        $menu = $event->getMenu();
        if ($this->authorizationChecker->isGranted('ROLE_USER')) {

            $menu->addChild('Manage System', array())
                ->setAttribute('dropdown', true)
                ->setAttribute('icon', 'fa fa-cog')
                ->setLinkAttribute('data-hover', 'dropdown');

            $menu['Manage System']->addChild('Items', array('route' => 'item'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('item') && !$this->isMatch('itemtype')) {
                $menu['Manage System']->getChild('Items')->setCurrent(true);
            }

            $menu['Manage System']->addChild('Item Types', array('route' => 'itemtype'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('itemtype')) {
                $menu['Manage System']->getChild('Item Types')->setCurrent(true);
            }

            $menu['Manage System']->addChild('Categories', array('route' => 'category'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('category')) {
                $menu['Manage System']->getChild('Categories')->setCurrent(true);
            }

            if (class_exists('Rbs\Bundle\SalesBundle\RbsPurchaseBundle')) {
                $menu['Manage System']->addChild('Sub Categories', array('route' => 'subcategory'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('subcategory')) {
                    $menu['Manage System']->getChild('Sub Categories')->setCurrent(true);
                }
            }

            $menu['Manage System']->addChild('Areas', array('route' => 'area'))
                ->setAttribute('icon', 'fa fa-map-marker');
            if ($this->isMatch('area')) {
                $menu['Manage System']->getChild('Areas')->setCurrent(true);
            }

            $menu['Manage System']->addChild('Projects', array('route' => 'project'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('project') && !$this->isMatch('projecttype')) {
                $menu['Manage System']->getChild('Projects')->setCurrent(true);
            }

            $menu['Manage System']->addChild('Project Types', array('route' => 'projecttype'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('projecttype')) {
                $menu['Manage System']->getChild('Project Types')->setCurrent(true);
            }

            $menu['Manage System']->addChild('Warehouses', array('route' => 'warehouse'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('warehouse')) {
                $menu['Manage System']->getChild('Warehouses')->setCurrent(true);
            }

            if (class_exists('Rbs\Bundle\SalesBundle\RbsPurchaseBundle')) {
                $menu['Manage System']->addChild('Cost Header', array('route' => 'cost_header'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('cost_header')) {
                    $menu['Manage System']->getChild('Cost Header')->setCurrent(true);
                }

                $menu['Manage System']->addChild('Vendors', array('route' => 'vendor'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('vendor')) {
                    $menu['Manage System']->getChild('Vendors')->setCurrent(true);
                }
            }
        }

        return $menu;
    }
}