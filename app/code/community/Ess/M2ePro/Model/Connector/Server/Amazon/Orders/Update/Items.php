<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Orders_Update_Items
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','update','entities');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Amazon_Orders_Update_ItemsResponser';
    }

    protected function getResponserParams()
    {
        $params = array();

        foreach ($this->params['items'] as $orderUpdate) {
            if (!is_array($orderUpdate)) {
                continue;
            }

            $params[$orderUpdate['change_id']] = $orderUpdate;
        }

        return $params;
    }

    // ########################################

    protected function setLocks($hash)
    {
        if (!isset($this->params['items']) || !is_array($this->params['items'])) {
            return;
        }

        $ordersIds = array();

        foreach ($this->params['items'] as $update) {
            if (!isset($update['order_id'])) {
                throw new LogicException('Order ID is not defined.');
            }

            $ordersIds[] = (int)$update['order_id'];
        }

        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
                ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK)
                ->addFieldToFilter('id', array('in' => $ordersIds))
                ->getItems();

        foreach ($orders as $order) {
            $order->addObjectLock('update_shipping_status', $hash);
        }
    }

    // ########################################

    protected function getRequestData()
    {
        if (!isset($this->params['items']) || !is_array($this->params['items'])) {
            return array('items' => array());
        }

        $orders = array();

        foreach ($this->params['items'] as $orderUpdate) {
            if (!is_array($orderUpdate)) {
                continue;
            }

            $fulfillmentDate = new DateTime($orderUpdate['fulfillment_date'], new DateTimeZone('UTC'));

            $order = array(
                'id'               => $orderUpdate['change_id'],
                'order_id'         => $orderUpdate['amazon_order_id'],
                'tracking_number'  => $orderUpdate['tracking_number'],
                'carrier_name'     => $orderUpdate['carrier_name'],
                'fulfillment_date' => $fulfillmentDate->format('c'),
                'items'            => array()
            );

            if (isset($orderUpdate['items']) && is_array($orderUpdate['items'])) {
                foreach ($orderUpdate['items'] as $item) {
                    $order['items'][] = array(
                        'item_code' => $item['amazon_order_item_id'],
                        'qty'       => (int)$item['qty']
                    );
                }
            }

            $orders[] = $order;
        }

        return array('items' => $orders);
    }

    // ########################################
}