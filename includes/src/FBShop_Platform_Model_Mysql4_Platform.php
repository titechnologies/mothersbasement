<?php

class FBShop_Platform_Model_Mysql4_Platform extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the platform_id refers to the key field in your database table.
        $this->_init('platform/platform', 'platform_id');
    }
}