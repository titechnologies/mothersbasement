<?php

class TM_SoldTogether_Model_Mysql4_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_map = array('fields' => array(
        'product_name'          => 'cpev1.value',
        'related_product_name'  => 'cpev2.value'
    ));

    protected function _construct()
    {
        $this->_init('soldtogether/customer');
    }
}