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
//        $childMenu = $menu->getChild('User');
//        $childMenu->addChild('Customer', array('route' => ''))
//                ->setAttribute('dropdown', true);
////                ->setAttribute('icon', 'fa fa-user')
////                ->setLinkAttribute('data-hover', 'dropdown');

        $menu['User']->addChild('Customer List', array('route' => 'customers_home'));
        $menu['User']->addChild('Customer Create', array('route' => 'customer_create'));

        return $menu;
        //}
    }
}