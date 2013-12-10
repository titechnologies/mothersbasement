<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/AdminhtmlSalesOrderViewItems.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ hkpMqaYpyBwCjgyZ('4269428f8c9ee3e84f740ef610dcbf60'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc.
*/

class Aitoc_Aitpreorder_Block_Rewrite_AdminhtmlSalesOrderViewItems extends Mage_Adminhtml_Block_Sales_Order_View_Items
{
    public function getItemHtml(Varien_Object $item)
    {

        $prnthtml = parent::getItemHtml($item);
        $itemData=unserialize($item->getData('product_options'));//$prnthtml=$prnthtml."<pre>".print_r($item->getData(''),1)."</pre>";
        if (($item->getData('product_type')=='bundle') && ($itemData['product_calculations'] && $itemData['shipment_type'] ))
        {
            $bundle_preorder=strpos($prnthtml,"class='bundlepreorder'");
            if($bundle_preorder>0)
            {
                $replace_start=strpos($prnthtml,'<td>'.__('Ordered'));
                if($replace_start>0)
                {
                   $prnthtml=substr_replace($prnthtml,'<td>'.__('Pre-Ordered'),$replace_start,strlen('<td>'.__('Ordered')));
                }
            }
        }

        if (isset($itemData['simple_sku']))
        {
            $original_product = Mage::getModel('catalog/product');
            $original_product_id = $original_product->getIdBySku($itemData['simple_sku']);
			$original_product->setStoreId($item->getOrder()->getStoreId());

            //FIX FOR WRONG STORE ID IN Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem::loadByProduct
            if (!Mage::registry('aitoc_order_refund_store_id'))
            {
                Mage::register('aitoc_order_refund_store_id', $item->getOrder()->getStoreId());
            }
            //END FIX

            $original_product->load($original_product_id);
            $original_product_id=$original_product->getId();

            if ($original_product_id)
            {
                if($original_product->getPreorder()==1)
                {
                    $prnthtml = str_replace('<td>'.__('Ordered'),'<td>'.__('Pre-Ordered'),$prnthtml);
                }
            }

        }
        else
        {
			$original_product = Mage::getModel('catalog/product');
			$original_product->setStoreId($item->getOrder()->getStoreId());

            //FIX FOR WRONG STORE ID IN Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem::loadByProduct
            if (!Mage::registry('aitoc_order_refund_store_id'))
            {
                Mage::register('aitoc_order_refund_store_id', $item->getOrder()->getStoreId());
            }
            //END FIX

			$original_product->load($item->getData('product_id'));

            if ($original_product->getPreorder()==1)
            {
                $prnthtml = str_replace('<td>'.__('Ordered'),'<td>'.__('Pre-Ordered'),$prnthtml);
            }


            $bundle_preorder_pos=strpos($prnthtml,"class='bundlepreorder'");
            while($bundle_preorder_pos>0)
            {
                $replace_start=strpos($prnthtml,'<td>'.__('Ordered'),$bundle_preorder_pos);
                if($replace_start>0)
                {
                   $prnthtml=substr_replace($prnthtml,'<td>'.__('Pre-Ordered'),$replace_start,strlen('<td>'.__('Ordered')));
                }
                $bundle_preorder_pos=strpos($prnthtml,"class='bundlepreorder'",$replace_start);
            }
		}

        return $prnthtml;

    }
} } 