<?php

namespace Rbs\Bundle\UserBundle\Permission\Provider;

class SecurityPermissionProvider implements ProviderInterface
{
    public function getPermissions()
    {
        return array(
            'USER' => array(
                'ROLE_USER_VIEW', 'ROLE_USER_CREATE', 'ROLE_ADMIN'
            ),

            'GROUP' => array(
                'ROLE_GROUP_VIEW', 'ROLE_GROUP_CREATE'
            ),
            
            'SALES' => array(
                'ROLE_RSM_GROUP', 'ROLE_SR_GROUP'
            ),

            'HEAD_OFFICE' => array(
                'ROLE_HEAD_OFFICE', 'ROLE_HEAD_OFFICE'
            )
        );
    }
}