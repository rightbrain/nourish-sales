<?php
namespace Rbs\Bundle\CoreBundle\EventListener;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

abstract class ContextAwareListener
{
    /** @var AuthorizationChecker */
    protected $authorizationChecker;

    /**
     * @param AuthorizationChecker $context
     */
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;

    }
}