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
                'ROLE_ORDER_VERIFY', 'ROLE_CHICK_ORDER_MANAGE', 'ROLE_FEED_ORDER_MANAGE'
            ),

            'PAYMENT' => array(
                'ROLE_PAYMENT_APPROVE', 'ROLE_PAYMENT_OVER_CREDIT_APPROVE', 'ROLE_PAYMENT_CREATE', 'ROLE_PAYMENT_VIEW'
            ),

            'AGENT' => array(
                'ROLE_AGENT', 'ROLE_AGENT_VIEW', 'ROLE_AGENT_CREATE', 'ROLE_AGENT_LEDGER_VIEW'
            ),

            'STOCK' => array(
                'ROLE_STOCK_VIEW', 'ROLE_STOCK_CREATE'
            ),

            'DELIVERY' => array(
                'ROLE_DELIVERY_MANAGE','ROLE_CHICK_DELIVERY_MANAGE'
            ),

            'TARGET' => array(
                'ROLE_TARGET_MANAGE'
            ),

            'INCENTIVE' => array(
                'ROLE_INCENTIVE_MANAGE'
            ),

            'CREDIT' => array(
                'ROLE_CREDIT_LIMIT_MANAGE'
            ),

            'CASH' => array(
                'ROLE_CASH_RECEIVE_MANAGE', 'ROLE_CASH_DEPOSIT_MANAGE'
            ),

            'SWAPPING' => array(
                'ROLE_SWAPPING_MANAGE'
            ),

            'DEPO' => array(
                'ROLE_DEPO_USER','ROLE_DEPO_MENU_ACCESS','ROLE_HATCHERY_USER','ROLE_HATCHERY_MENU_ACCESS'
            ),

            'HEAD_OFFICE' => array(
                'ROLE_HEAD_OFFICE_USER'
            ),

            'REPORT' => array(
                'ROLE_SALES_REPORT','ROLE_FEED_ORDER_REPORT','ROLE_DEPOT_FEED_ORDER_REPORT','ROLE_REGION_FEED_ORDER_REPORT','ROLE_FEED_DELIVERY_REPORT','ROLE_CHICK_DELIVERY_REPORT'
            ),

            'BANK_SLIP' => array(
                'ROLE_BANK_SLIP_VERIFIER', 'ROLE_BANK_SLIP_APPROVAL'
            ),

            'TRUCK' => array(
                'ROLE_TRUCK_MANAGE', 'ROLE_TRUCK_IN', 'ROLE_TRUCK_OUT', 'ROLE_TRUCK_START', 'ROLE_TRUCK_FINISH'
            ),

            'TRUCK_CHICK' => array(
                'ROLE_CHICK_TRUCK_MANAGE', 'ROLE_CHICK_TRUCK_IN', 'ROLE_CHICK_TRUCK_OUT', 'ROLE_CHICK_TRUCK_START', 'ROLE_CHICK_TRUCK_FINISH'
            ),

            'SETTING' => array(
                'ROLE_CORE_SETTING'
            )

        );
    }
}