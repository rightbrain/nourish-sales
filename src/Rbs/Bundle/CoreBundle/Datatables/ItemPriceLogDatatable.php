<?php

namespace Rbs\Bundle\CoreBundle\Datatables;

use Rbs\Bundle\CoreBundle\Entity\ItemPriceLog;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

/**
 * Class ItemPriceLogDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class ItemPriceLogDatatable extends BaseDatatable
{
    /** @var Request */
    protected $request;
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $securityToken,
        Twig_Environment $twig,
        TranslatorInterface $translator,
        RouterInterface $router,
        EntityManagerInterface $em,
        ContainerInterface $container
    )
    {
        $template = array(
            'base' => 'SgDatatablesBundle:Datatable:datatable.html.twig',
            'html' => 'SgDatatablesBundle:Datatable:datatable_html.html.twig',
            'js' => 'SgDatatablesBundle:Datatable:datatable_js.html.twig',
        );
        parent::__construct($authorizationChecker, $securityToken, $twig, $translator, $router, $em, $template);

        $this->request = $container->get('request');
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $itemId = $this->request->get('item');
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('items_price_log_list_ajax', array('item' => $itemId)),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('item.name', 'column', array('title' => 'Item Name'))
            ->add('currentPrice', 'column', array('title' => 'Current Price'))
            ->add('previousPrice', 'column', array('title' => 'Previous Price'))
            ->add('updatedAt', 'datetime', array('title' => 'Update Date',
                    'date_format' => 'LLL' ))
            ->add('updatedBy.profile.fullName', 'column', array('title' => 'Update By'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\CoreBundle\Entity\ItemPriceLog';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'item_price_log_datatable';
    }
}
