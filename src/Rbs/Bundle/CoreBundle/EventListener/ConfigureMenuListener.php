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

        if ($this->authorizationChecker->isGranted(array('ROLE_AGENT'))) {
            return $menu;
        }

        $menu->addChild('Manage System', array())
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-cog')
            ->setLinkAttribute('data-hover', 'dropdown');

        if ($this->authorizationChecker->isGranted('ROLE_ITEM_MANAGE')) {
            $menu['Manage System']->addChild('Items', array('route' => 'item'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('item') && !$this->isMatch('itemtype')) {
                $menu['Manage System']->getChild('Items')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_ITEM_TYPE_MANAGE')) {
            $menu['Manage System']->addChild('Item Types', array('route' => 'itemtype'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('itemtype')) {
                $menu['Manage System']->getChild('Item Types')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_ITEM_MANAGE')) {
            $menu['Manage System']->addChild('Categories', array('route' => 'category'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('category')) {
                $menu['Manage System']->getChild('Categories')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_SUB_CATEGORY_MANAGE')) {
            $menu['Manage System']->addChild('Sub Categories', array('route' => 'subcategory'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('subcategory')) {
                $menu['Manage System']->getChild('Sub Categories')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_AREA_MANAGE')) {
            $menu['Manage System']->addChild('Areas', array('route' => 'area'))
                ->setAttribute('icon', 'fa fa-map-marker');
            if ($this->isMatch('area')) {
                $menu['Manage System']->getChild('Areas')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_FACTORY_MANAGE', 'ROLE_PROJECT_MANAGE'))) {
            $menu['Manage System']->addChild('Factories', array('route' => 'project'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('project') && !$this->isMatch('projecttype')) {
                $menu['Manage System']->getChild('Factories')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_PROJECT_TYPE_MANAGE', 'ROLE_FACTORY_TYPE_MANAGE'))) {
            $menu['Manage System']->addChild('Factory Types', array('route' => 'projecttype'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('projecttype')) {
                $menu['Manage System']->getChild('Factory Types')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_DEPO_MANAGE')) {
            $menu['Manage System']->addChild('Depos', array('route' => 'depo'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('depo')) {
                $menu['Manage System']->getChild('Depos')->setCurrent(true);
            }

        }

        if ($this->authorizationChecker->isGranted('ROLE_COST_HEADER_MANAGE')) {
            $menu['Manage System']->addChild('Cost Header', array('route' => 'cost_header'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('cost_header')) {
                $menu['Manage System']->getChild('Cost Header')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_VENDOR_MANAGE')) {
            $menu['Manage System']->addChild('Vendors', array('route' => 'vendor'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('vendor')) {
                $menu['Manage System']->getChild('Vendors')->setCurrent(true);
            }
        }

        if (empty($menu->getChild('Manage System')->getChildren())) {
            $menu->removeChild($menu['Manage System']);
        }

        return $menu;
    }
}