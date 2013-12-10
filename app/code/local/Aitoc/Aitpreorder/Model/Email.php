<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Model/Email.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ cmhBCkYhMjoIZeMr('11f10bc75b19be7ecea3d9c1f8236013'); ?><?php
/**
 * @copyright  Copyright (c) 2012 AITOC, Inc. 
 */
class Aitoc_Aitpreorder_Model_Email extends Mage_ProductAlert_Model_Email
{ 
    protected function _getStockBlock()
    {
        if (is_null($this->_stockBlock)) {
            $this->_stockBlock = Mage::helper('productalert')
                ->createBlock('aitpreorder/email_stock');
        }
		return $this->_stockBlock;
    }
} } 