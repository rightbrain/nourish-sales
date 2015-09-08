<?php
namespace Rbs\Bundle\UserBundle\EventListener;

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

            $menu->addChild('User', array('route' => ''))
                ->setAttribute('dropdown', true)
                ->setAttribute('icon', 'fa fa-user')
                ->setLinkAttribute('data-hover', 'dropdown');

            $menu['User']->addChild('User List', array('route' => 'users_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            $menu['User']->addChild('User Create', array('route' => 'user_create'))
                ->setAttribute('icon', 'fa fa-th-list');
            $menu['User']->addChild('Group List', array('route' => 'groups_home'))
                ->setAttribute('icon', 'fa fa-th-list');
            $menu['User']->addChild('Group Create', array('route' => 'group_create'))
                ->setAttribute('icon', 'fa fa-th-list');

        return $menu;
        //}
    }
}