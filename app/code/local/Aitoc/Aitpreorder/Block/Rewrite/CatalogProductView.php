<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/CatalogProductView.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ IMCecESwgDpUajgk('834630676e6a28094714d33b719ea212'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpreorder_Block_Rewrite_CatalogProductView extends Mage_Catalog_Block_Product_View
{
    protected function _toHtml()
    {          
        if(($this->getNameInLayout()=='product.info') && $this->getProduct()->getPreorder())
        {
            $result=parent::_toHtml();
            $descript=$this->getProduct()->getPreorderdescript();
            if($descript=="")
            {
                $descript=$this->__('Pre-Order');
            }
            $result=str_ireplace($this->__('In stock')," ".$descript,$result);
            $result=str_ireplace($this->__('Out stock')," ".$this->__('not Available'),$result);
            $result=str_ireplace($this->__('Add to Cart'),$this->__('Pre Order'),$result);
            $result=str_ireplace('<div class="pricenew">Price -','<div class="pricenew">PRE-ORDER Price -',$result);
            return($result);
        }
        elseif($this->getNameInLayout()=='product.info.addtocart' && $this->getProduct()->getPreorder())
        {
            //$result=str_replace('title="Add to Cart" class="button btn-cart"', 'title="Pre-Order" class="buttonpreorder"', parent::_toHtml());
            $result=parent::_toHtml();
            $result=str_ireplace('class="button btn-cart"','class="buttonpreorder"',$result);
            $result=str_ireplace('title="Add to Cart"','title="Pre-Order"',$result);
            return $result;
        }
        else
        { 
            return (parent::_toHtml());
        }
    }
} } 