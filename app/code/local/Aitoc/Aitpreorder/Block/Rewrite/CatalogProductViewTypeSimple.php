<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/CatalogProductViewTypeSimple.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ NDRrQdYhZEofWkZB('2b1ca8e08cd929a48f1b6200ca18d53f'); ?><?php
class Aitoc_Aitpreorder_Block_Rewrite_CatalogProductViewTypeSimple extends Mage_Catalog_Block_Product_View_Type_Simple
{
    protected function _toHtml()
    {
        $product = $this->getProduct();        
        if ($product->getPreorder())
        {
            $catalogHelper = Mage::helper('catalog');
            return str_replace(array($catalogHelper->__('In stock'), $catalogHelper->__('Out of stock'), ), Mage::helper('aitpreorder')->__('Pre-Order'), parent::_toHtml());
        }
        
        return parent::_toHtml();
    }
} } 