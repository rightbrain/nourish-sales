<?php
namespace Rbs\Bundle\CoreBundle\EventListener;

use Knp\Menu\MenuItem;
use Rbs\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Rbs\Bundle\UserBundle\Entity\User;

class ConfigureMenuListener extends ContextAwareListener
{
    /**
     * @param ConfigureMenuEvent $event
     * @return MenuItem
     */
    public function onMenuConfigureMain(ConfigureMenuEvent $event)
    {
        if ($this->user->getUserType() == User::USER or $this->user->getUserType() == User::ZM) {
            /** @var MenuItem $menu */
            $menu = $event->getMenu();

            if ($this->authorizationChecker->isGranted(array('ROLE_AGENT'))) {
                return $menu;
            }

            $menu->addChild('Settings', array())
                ->setAttribute('dropdown', true)
                ->setAttribute('icon', 'fa fa-cog')
                ->setLinkAttribute('data-hover', 'dropdown');

            if ($this->authorizationChecker->isGranted('ROLE_ITEM_MANAGE')) {
                $menu['Settings']->addChild('Items', array('route' => 'item'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('item') && !$this->isMatch('itemtype')) {
                    $menu['Settings']->getChild('Items')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_ITEM_TYPE_MANAGE')) {
                $menu['Settings']->addChild('Item Types', array('route' => 'itemtype'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('itemtype')) {
                    $menu['Settings']->getChild('Item Types')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_ITEM_MANAGE')) {
                $menu['Settings']->addChild('Categories', array('route' => 'category'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('category')) {
                    $menu['Settings']->getChild('Categories')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_LOCATION_MANAGE')) {
                $menu['Settings']->addChild('Locations', array('route' => 'locations'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('locations')) {
                    $menu['Settings']->getChild('Locations')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_DEPO_MANAGE')) {
                $menu['Settings']->addChild('Depos', array('route' => 'depo'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('depo')) {
                    $menu['Settings']->getChild('Depos')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_SALE_INCENTIVE_MANAGE')) {
                $menu['Settings']->addChild('Sale Incentive', array('route' => 'sale_incentive_list'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('sale_incentive_list') or $this->isMatch('sale_incentive_import')) {
                    $menu['Settings']->getChild('Sale Incentive')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_TRANSPORT_INCENTIVE_MANAGE')) {
                $menu['Settings']->addChild('Transport Commission', array('route' => 'transport_incentive_list'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('transport_incentive_list') or $this->isMatch('transport_incentive_import')) {
                    $menu['Settings']->getChild('Transport Commission')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                $menu['Settings']->addChild('SMS Emulator', array('route' => 'order_via_sms'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('order_via_sms')) {
                    $menu['Settings']->getChild('SMS Emulator')->setCurrent(true);
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                $menu['Settings']->addChild('Vehicle SMS Emulator', array('route' => 'vehicle_via_sms'))
                    ->setAttribute('icon', 'fa fa-th-list');
                if ($this->isMatch('vehicle_via_sms')) {
                    $menu['Settings']->getChild('Vehicle SMS Emulator')->setCurrent(true);
                }
            }

            if (empty($menu->getChild('Settings')->getChildren())) {
                $menu->removeChild($menu['Settings']);
            }
            return $menu;
        }
    }
}