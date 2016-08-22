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

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN', 'ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL'))) {
            $menu['Sales']->addChild('Orders', array('route' => 'orders_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('orders') or $this->isMatch('order_details') or $this->isMatch('order_create') or $this->isMatch('order_update')) {
                $menu['Sales']->getChild('Orders')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL'))) {
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

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Payments', array('route' => 'payments_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('payment')) {
                $menu['Sales']->getChild('Payments')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) {
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
            if ($this->isMatch('targets') or $this->isMatch('target_create') or $this->isMatch('target_update')) {
                $menu['Sales']->getChild('Target')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_REPORT', 'ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Report', array('route' => 'reports_home'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->user->getUserType() == User::RSM){
            $menu['Sales']->addChild('RSM', array('route' => 'target_my'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Cash Receive', array('route' => 'cash_receive_list'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('cash_receive_create') or $this->isMatch('cash_receive_list')) {
                $menu['Sales']->getChild('Cash Receive')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('RSM Swapping', array('route' => 'swapping_rsm_list'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('swapping_rsm_create') or $this->isMatch('swapping_rsm_list')) {
                $menu['Sales']->getChild('RSM Swapping')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('SR Swapping', array('route' => 'swapping_sr_list'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('swapping_sr_create') or $this->isMatch('swapping_sr_list')) {
                $menu['Sales']->getChild('SR Swapping')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_DEPO_USER'))) {
            $menu['Sales']->addChild('Cash Deposit', array('route' => 'cash_deposit_list'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('cash_deposit_list') or $this->isMatch('cash_deposit_create')) {
                $menu['Sales']->getChild('Cash Deposit')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Agents Ledger', array('route' => 'agents_laser'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->user->getUserType() == User::USER and $this->authorizationChecker->isGranted(array('ROLE_DEPO_USER'))) {
            $menu['Sales']->addChild('Cash Receive', array('route' => 'cash_receive_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Cash Receive From Depo', array('route' => 'cash_receive_from_depo_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_AGENT'))) {
            $menu['Sales']->addChild('Truck List', array('route' => 'truck_info_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Credit Limit', array('route' => 'credit_limit_list'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('credit_limit_list') or $this->isMatch('credit_limit_create') or $this->isMatch('credit_limit_notification_list')) {
                $menu['Sales']->getChild('Credit Limit')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Damage Good', array('route' => 'damage_good_admin_list'))
                ->setAttribute('icon', 'fa fa-th-list');
            if ($this->isMatch('damage_good_form') or $this->isMatch('damage_good_list')) {
                $menu['Sales']->getChild('Damage Good')->setCurrent(true);
            }
        }

        if ($this->authorizationChecker->isGranted(array('ROLE_ADMIN'))) {
            $menu['Sales']->addChild('Incentive', array('route' => 'incentives_home'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->user->getUserType() == User::SR and !$this->authorizationChecker->isGranted(array('ROLE_ADMIN'))){
            $menu['Sales']->addChild('Damage Good', array('route' => 'damage_good_list'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        if ($this->user->getUserType() == User::AGENT){
            $menu['Sales']->addChild('My Truck List', array('route' => 'truck_info_my_list'))
                ->setAttribute('icon', 'fa fa-th-list');

            $menu['Sales']->addChild('My Orders', array('route' => 'orders_my_home'))
                ->setAttribute('icon', 'fa fa-th-list');

            $menu['Sales']->addChild('Bank Slip', array('route' => 'bank_info_list'))
                ->setAttribute('icon', 'fa fa-th-list');

            $menu['Sales']->addChild('My Laser', array('route' => 'my_laser'))
                ->setAttribute('icon', 'fa fa-th-list');

            $menu['Sales']->addChild('My Doc', array('route' => 'my_doc'))
                ->setAttribute('icon', 'fa fa-th-list');
        }

        return $menu;
    }
}