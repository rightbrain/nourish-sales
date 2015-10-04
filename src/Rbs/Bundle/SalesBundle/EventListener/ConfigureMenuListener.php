<?php
namespace Rbs\Bundle\SalesBundle\EventListener;

use Knp\Menu\MenuItem;
use Rbs\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Rbs\Bundle\CoreBundle\EventListener\ContextAwareListener;

class ConfigureMenuListener extends ContextAwareListener
{
    /**
     * @param ConfigureMenuEvent $event
     * @return MenuItem
     */
    public function onMenuConfigureMain(ConfigureMenuEvent $event)
    {
        /** @var MenuItem $menu */
        $menu = $event->getMenu();
        $menu->addChild('Sales', array('route' => ''))
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-bar-chart-o')
            ->setLinkAttribute('data-hover', 'dropdown');

        if ($this->authorizationChecker->isGranted(array('ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL'))) {
            $menu['Sales']->addChild('Orders', array('route' => 'orders_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('order')) {
                $menu['Sales']->getChild('Orders')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL'))) {
            $menu['Sales']->addChild('Deliveries', array('route' => 'deliveries_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('deliver')) {
                $menu['Sales']->getChild('Deliveries')->setCurrent(true);
            }
        }

            $menu['Sales']->addChild('Stocks', array('route' => 'stocks_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('stock')) {
                $menu['Sales']->getChild('Stocks')->setCurrent(true);
            }

        if ($this->authorizationChecker->isGranted(array('ROLE_ORDER_VIEW', 'ROLE_PAYMENT_CREATE', 'ROLE_PAYMENT_APPROVE', 'ROLE_PAYMENT_OVER_CREDIT_APPROVE'))) {
            $menu['Sales']->addChild('Payments', array('route' => 'payments_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('payment')) {
                $menu['Sales']->getChild('Payments')->setCurrent(true);
            }
        }

        $menu['Sales']->addChild('Customers', array('route' => 'customers_home'))
            ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('customer')) {
                $menu['Sales']->getChild('Customers')->setCurrent(true);
            }

        return $menu;
    }
}