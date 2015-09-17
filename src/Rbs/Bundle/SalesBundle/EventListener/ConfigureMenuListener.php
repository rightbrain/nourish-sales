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
        //if ($this->authorizationChecker->isGranted('ROLE_DOCUMENT_ACCESS')) {

        $menu->addChild('Stock', array('route' => ''))
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-suitcase')
            ->setLinkAttribute('data-hover', 'dropdown');

        $menu['Stock']->addChild('Stock List', array('route' => 'stocks_home'))
            ->setAttribute('icon', 'fa fa-th-list');

        $menu->addChild('Order', array('route' => ''))
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-suitcase')
            ->setLinkAttribute('data-hover', 'dropdown');

        $menu['Order']->addChild('Order List', array('route' => 'orders_home'))
            ->setAttribute('icon', 'fa fa-th-list');

        $menu->addChild('Payment', array('route' => ''))
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-suitcase')
            ->setLinkAttribute('data-hover', 'dropdown');

        $menu['Payment']->addChild('Payment List', array('route' => 'payments_home'))
            ->setAttribute('icon', 'fa fa-th-list');

        $menu['User']->addChild('Customer List', array('route' => 'customers_home'))
            ->setAttribute('icon', 'fa fa-th-list');
        $menu['User']->addChild('Customer Create', array('route' => 'customer_create'))
            ->setAttribute('icon', 'fa fa-th-list');

        return $menu;
        //}
    }
}