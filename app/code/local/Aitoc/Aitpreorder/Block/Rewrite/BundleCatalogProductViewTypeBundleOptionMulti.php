<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/BundleCatalogProductViewTypeBundleOptionMulti.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ QjTaRVSqDkiNPrDg('2c68cc9afbfd247b6f6d311ebab9f53f'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpreorder_Block_Rewrite_BundleCatalogProductViewTypeBundleOptionMulti extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi                                          
{
  
   public function getSelectionQtyTitlePrice($_selection, $includeContainer = true)
    {
        $addinf='';
        $_product = Mage::getModel('catalog/product')->load($_selection->getId());
        if($_product->getPreorder()==1)
        {
            $addinf=__('Pre-Order');
        }
        return parent::getSelectionQtyTitlePrice($_selection, $includeContainer).'  <span class="price-notice">'.$addinf.'</span>';  
    } 

} } 