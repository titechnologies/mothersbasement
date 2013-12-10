<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/AdminhtmlSalesOrderShipmentCreateForm.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ ReUZTOYpjrwQEajM('f9de676ef929221e45411e01a823241c'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc.
*/

class Aitoc_Aitpreorder_Block_Rewrite_AdminhtmlSalesOrderShipmentCreateForm extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Form
{
    protected function _toHtml()
    {

        $result = parent::_toHtml();

        /*
        $pattern2='/'.__("SKU:").'<\/strong>\s.{2,40}<\/div>/';
        $count2=preg_match($pattern2,$result,$matches_nosimple,PREG_OFFSET_CAPTURE);
       */
        $pattern2='/id="preordersku" value=".{2,40}">/';
        $count2=preg_match($pattern2,$result,$matches_nosimple,PREG_OFFSET_CAPTURE);

        $pattern = '/name="shipment\[items\]\[[0-9]{1,7}\]"\svalue="[0-9]{1,7}"\s\//U';
        
        while($count2>0)
        {

            $count=preg_match($pattern, $result,$matches, PREG_OFFSET_CAPTURE,$matches_nosimple[0][1]);
            
        	$start=strlen('id="preordersku" value="');
        	preg_match('/.{2,40}">$/', substr($matches_nosimple[0][0],$start),$forSku);
            $SkuInOrder=substr($forSku[0],0,-2);


            $original_product = Mage::getModel('catalog/product');
            $original_product_id = $original_product->getIdBySku($SkuInOrder);
			$original_product->setStoreId($this->getOrder()->getStoreId());

            //FIX FOR WRONG STORE ID IN Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem::loadByProduct
            if (!Mage::registry('aitoc_order_refund_store_id'))
            {
                Mage::register('aitoc_order_refund_store_id', $this->getOrder()->getStoreId());
            }

            $original_product->load($original_product_id);

            preg_match('/[0-9]{1,7}/',$matches[0][0],$forid);
            $idInOrder=$forid[0];

            if($original_product->getId())
            {
                if($original_product->getPreorder()==1)
        	    {

                    $result=str_replace('type="text" class="input-text" '.$matches[0][0],'type="hidden" class="input-text" name="shipment[items]['.$idInOrder.']" value="0" /><div>'.__('This product is Pre-Order and cannot be shipped').'</div',$result);

                }
        	}
           $count2=preg_match($pattern2,$result,$matches_nosimple,PREG_OFFSET_CAPTURE,$matches_nosimple[0][1]+strlen($matches_nosimple[0][0]));
           $count=preg_match($pattern, $result,$matches, PREG_OFFSET_CAPTURE,$matches[0][1]+strlen($matches[0][0]));
        }

        $match = array();
        $pattern = '/(<tr.*class=\'bundlepreorder\'.*)(<input type="text" class="input-text" name="shipment\[items\]\[(.*)\]" value=".*" \/>)(.*<\/tr>)/smiU';
        $result = preg_replace_callback($pattern, array( &$this, 'replaceBundle'), $result);
        return($result);
   }

   public function replaceBundle($matches)
   {
       $res = $matches[1];
       $res .= '<input type="hidden" class="input-text" name="shipment[items]['.$matches[3].']" value="0" /><div style="text-align:center;">'.$this->__('This product is Pre-Order and cannot be shipped').'</div>';
       $res .= $matches[4];
       return $res;
   }
   
} } 