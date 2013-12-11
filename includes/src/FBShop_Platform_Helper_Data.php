<?php
/**
 * 
 * @category    Mage
 * @package     Mage_Api
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class FBShop_Platform_Helper_Data extends Mage_Core_Helper_Abstract
{

 public function _construct()
    {
        parent::_construct();
      
        $this->_init('Platform/Platform');
        //$this->user=$this->guid();
       
    }
	
}
