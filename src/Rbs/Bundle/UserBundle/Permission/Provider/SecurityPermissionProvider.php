<?php

namespace Rbs\Bundle\UserBundle\Permission\Provider;

class SecurityPermissionProvider implements ProviderInterface
{
    public function getPermissions()
    {
        return array(
            'ROLE_ADMIN_USER' => array('ROLE_VIEW', 'ROLE_ADD', 'ROLE_UPDATE', 'ROLE_DELETE', 'ROLE_RESTORE'),
        );
    }
}