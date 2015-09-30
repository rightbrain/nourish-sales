<?php

namespace Rbs\Bundle\UserBundle\Permission\Provider;

class SecurityPermissionProvider implements ProviderInterface
{
    public function getPermissions()
    {
        return array(
            'USER' => array(
                'ROLE_USER_VIEW', 'ROLE_USER_CREATE'
            ),

            'GROUP' => array(
                'ROLE_GROUP_VIEW', 'ROLE_GROUP_CREATE'
            )
        );
    }
}