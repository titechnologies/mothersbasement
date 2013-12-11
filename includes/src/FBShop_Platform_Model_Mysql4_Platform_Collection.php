<?php

class FBShop_Platform_Model_Mysql4_Platform_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('platform/platform');
    }
}