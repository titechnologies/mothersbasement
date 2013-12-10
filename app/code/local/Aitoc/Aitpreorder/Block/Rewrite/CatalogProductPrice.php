<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/CatalogProductPrice.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ cmhBCkYhMjoIZeMr('295ba79798e3accf1023e1ded91062eb'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpreorder_Block_Rewrite_CatalogProductPrice extends Mage_Catalog_Block_Product_Price
{

    protected function _toHtml()
    {   
        $_product = $this->getProduct();
        $preOrderFlag = false;
        $inStock = false;
        if($_product->getTypeId() == 'configurable')
        {
           // $opt = new Aitoc_Aitpreorder_Block_Rewrite_CatalogProductViewTypeConfigurable();
           // echo var_dump($opt->getJsonConfigWithPreorder());

            $associatedProducts = Mage::getSingleton('catalog/product_type')->factory($_product)->getUsedProducts($_product);
            foreach($associatedProducts as $associatedProduct) 
            {   
                if($associatedProduct->getPreorder() == '1')
                {
                    $preOrderFlag = true;
                } else {
					if ($associatedProduct->getData('is_in_stock')) {
						$inStock = true;
					}
				}
            } 
        }

		if ($inStock) {
			$preOrderFlag = false;
		}
        
        $_id = $_product->getId(); 
        $add_preorder_before_price="";
        
        if(Mage::getModel('catalog/product')->load($_id)->getPreorder()==1 or $preOrderFlag)
        {
            return(str_replace('price-box"','price-box"><span class="regular-price price pre-order"></span', parent::_toHtml()));
        }
                else
        {        
            return ($add_preorder_before_price.parent::_toHtml());
        }
    }

} } 