<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */ 
class Amasty_Purchases_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _beforeToHtml()
    {
        $this->addTab('ampurchases', array(
            'label'     => Mage::helper('ampurchases')->__('Orders with Product'),
            'class'     => 'ajax',
            'url'       => $this->getUrl('ampurchases/adminhtml_purchases/orders', array('_current' => true)),
        ));
        
        if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Label/active')){
            $name = Mage::helper('amlabel')->__('Product Labels');
            $this->addTab('general', array(
                'label'     => $name,
                'content'   => $this->getLayout()->createBlock('amlabel/adminhtml_catalog_product_edit_labels')
                    ->setTitle($name)->toHtml(),
            ));   
        }
        
        return parent::_beforeToHtml();
    }
}