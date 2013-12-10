<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Model/Rewrite/SalesOrderShipment.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ ReUZTOYpjrwQEajM('430a7bdbdd0a822d907d4002c033dfeb'); ?><?php
/**
 * @copyright  Copyright (c) 2010 AITOC, Inc. 
 */
class Aitoc_Aitpreorder_Model_Rewrite_SalesOrderShipment extends Mage_Sales_Model_Order_Shipment
{
    public function addItem(Mage_Sales_Model_Order_Shipment_Item $item)
    {
        $item->setShipment($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if(!$item->getId()) {
            $orderItem=$item->getOrderItem();
            if($orderItem->getData('product_type')=='bundle')
            {
                $isRegular=0;
                $isRegular=Mage::helper('aitpreorder')->bundleHaveReg($orderItem);
                if($isRegular==0)
                {
                    if (version_compare(Mage::getVersion(),'1.4.1.0','>=')) 
                    {
                        $tmp_total_qty=$this->getTotalQty();
                        $this->setTotalQty($tmp_total_qty-$item->getQty());
                    }
                    $item->setQty(0);
                }
            }
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }
} } 