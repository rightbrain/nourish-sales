<?php
namespace Rbs\Bundle\SalesBundle\EventListener;

use Knp\Menu\MenuItem;
use Rbs\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Rbs\Bundle\CoreBundle\EventListener\ContextAwareListener;
use Rbs\Bundle\UserBundle\Entity\User;

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

        if ($this->authorizationChecker->isGranted(array('ROLE_AGENT', 'ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL'))) {
            $menu['Sales']->addChild('Orders', array('route' => 'orders_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('orders')) {
                $menu['Sales']->getChild('Orders')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_AGENT', 'ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL'))) {
            $menu['Sales']->addChild('Orders From SMS', array('route' => 'order_readable_sms'))
                ->setAttribute('icon', 'fa fa-th-list');

        }

        if ($this->authorizationChecker->isGranted(array('ROLE_DELIVERY_MANAGE'))) {
            $menu['Sales']->addChild('Deliveries', array('route' => 'deliveries_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('deliver')) {
                $menu['Sales']->getChild('Deliveries')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_STOCK_VIEW', 'ROLE_STOCK_CREATE'))) {
            $menu['Sales']->addChild('Stocks', array('route' => 'stocks_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('stock')) {
                $menu['Sales']->getChild('Stocks')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_AGENT', 'ROLE_PAYMENT_VIEW', 'ROLE_PAYMENT_CREATE', 'ROLE_PAYMENT_APPROVE', 'ROLE_PAYMENT_OVER_CREDIT_APPROVE'))) {
            $menu['Sales']->addChild('Payments', array('route' => 'payments_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('payment')) {
                $menu['Sales']->getChild('Payments')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_AGENT', 'ROLE_AGENT_VIEW', 'ROLE_AGENT_CREATE'))) {
            $menu['Sales']->addChild('Agents', array('route' => 'agents_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('agent')) {
                $menu['Sales']->getChild('Agents')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ORDER_VIEW'))) {
            $menu['Sales']->addChild('Unread SMS', array('route' => 'sms_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('sms')) {
                $menu['Sales']->getChild('Unread SMS')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_SUPER_ADMIN', 'ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Target', array('route' => 'target_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_REPORT', 'ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Report', array('route' => 'reports_home'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_RSM_GROUP'))) {
            $menu['Sales']->addChild('RSM', array('route' => 'target_my'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_SR_GROUP'))) {
            $menu['Sales']->addChild('SR', array('route' => 'target_sr_my'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Cash Receive', array('route' => 'cash_receive_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Cash Deposit', array('route' => 'cash_deposit_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_HEAD_OFFICE'))) {
            $menu['Sales']->addChild('Cash Receive From Depo', array('route' => 'cash_receive_from_depo_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->user->getUserType() == User::AGENT){
            $menu['Sales']->addChild('My Orders', array('route' => 'orders_my_home'))
                ->setAttribute('icon', 'fa fa-th-list');

            $menu['Sales']->addChild('Bank Slip', array('route' => 'bank_info_list'))
                ->setAttribute('icon', 'fa fa-th-list');

            $menu['Sales']->addChild('Damage Good', array('route' => 'damage_good_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        return $menu;
    }
}