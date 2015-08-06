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
        //if ($this->authorizationChecker->isGranted('ROLE_DOCUMENT_ACCESS')) {

            $menu->addChild('Manage System', array())
                ->setAttribute('dropdown', true)
                ->setAttribute('icon', 'fa fa-cog')
                ->setLinkAttribute('data-hover', 'dropdown');
            $menu['Manage System']->addChild('Category', array('route' => 'category'))
                ->setAttribute('icon', 'fa fa-th-list');
            return $menu;
        //}
    }
}