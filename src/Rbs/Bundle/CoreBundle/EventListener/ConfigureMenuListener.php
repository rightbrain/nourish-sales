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

            $menu->addChild('Hello World', array('route' => 'homepage'))
                ->setAttribute('dropdown', true)
                ->setLinkAttribute('data-hover', 'dropdown');
            $menu['Hello World']->addChild('Home', array('uri' => '#'));
            return $menu;
        //}
    }
}