<?php

namespace Rbs\Bundle\SalesBundle\Permission\Provider;

use Rbs\Bundle\UserBundle\Permission\Provider\ProviderInterface;

class SecurityPermissionProvider implements ProviderInterface
{
    public function getPermissions()
    {
        return array(
            'ORDER' => array(
                'ROLE_ORDER_VIEW', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_APPROVE', 'ROLE_ORDER_CANCEL',
                'ROLE_ORDER_VERIFY'
            ),

            'PAYMENT' => array(
                'ROLE_PAYMENT_APPROVE', 'ROLE_PAYMENT_OVER_CREDIT_APPROVE', 'ROLE_PAYMENT_CREATE', 'ROLE_PAYMENT_VIEW'
            ),

            'CUSTOMER' => array(
                'ROLE_CUSTOMER_VIEW', 'ROLE_CUSTOMER_CREATE'
            ),

            'STOCK' => array(
                'ROLE_STOCK_VIEW', 'ROLE_STOCK_CREATE'
            ),

            'DELIVERY' => array(
                'ROLE_DELIVERY_MANAGE'
            )

        );
    }
}