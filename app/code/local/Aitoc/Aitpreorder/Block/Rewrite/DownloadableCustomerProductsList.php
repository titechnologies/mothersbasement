<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/DownloadableCustomerProductsList.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ hkpMqaYpyBwCjgyZ('0c0823628341fdcce8610b4007317eac'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpreorder_Block_Rewrite_DownloadableCustomerProductsList extends Mage_Downloadable_Block_Customer_Products_List
{

    public function getDownloadUrl($item)
    {
        $sku=$item->getPurchased()->getData('product_sku');
        $product = Mage::getModel('catalog/product');
        $product_id = $product->getIdBySku($sku);
        $product->load($product_id);     
        $typeid=$product->getData('type_id');
        if(($product->getPreorder()==1)&&($typeid=='downloadable')&&($item->getStatus()==__('available')))
        {
            return 'zxc'.$product_id.'zxc';
              
        }
        else
        {
            return parent::getDownloadUrl($item);
        }  
    }
    
    protected function _toHtml()
    {
        $result=parent::_toHtml();
 
        $count=preg_match('/zxc[0-9]{1,10}zxc/',$result,$matches,PREG_OFFSET_CAPTURE);
        
   
       
        while($count>0)
        {
            $countahref=preg_match('/\<a.{1,100}'.$matches[0][0].'.{1,100}a\>/U',$result,$matchesahref,PREG_OFFSET_CAPTURE);
            $nameofvirtual='';
            preg_match('/\>.{1,60}\</',$matchesahref[0][0],$nameofvirtual);
            $result=substr_replace($result,substr($nameofvirtual[0],1,-1),$matchesahref[0][1],strlen($matchesahref[0][0]));
            $id=substr($matches[0][0],3,-3);
            $_product = Mage::getModel('catalog/product')->load($id);
            $relize=$_product->getPreorderdescript();
            $available_pos=strpos($result,ucfirst(__('available')),$matches[0][1]);
            if($available_pos>0)
            {
               $result=substr_replace($result,$relize,$available_pos,strlen(__('available')));
            }
            $count=preg_match('/zxc[0-9]{1,10}zxc/',$result,$matches,PREG_OFFSET_CAPTURE,$matches[0][1]+strlen($matches[0][0]));
        }
       
       return $result;
    }
    
} } 