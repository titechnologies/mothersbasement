<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */ 
class Amasty_Purchases_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{
    protected function _beforeToHtml()
    {
        if (Mage::registry('current_customer')->getId()) {
            $this->addTab('ampurchases', array(
                'label'     => Mage::helper('ampurchases')->__('Ordered Products'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('ampurchases/adminhtml_purchases/products', array('_current' => true)),
                'after'     => 'addresses',
            ));
        }
        return parent::_beforeToHtml();
    }
}