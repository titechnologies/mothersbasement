<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\   FME Layaway extension  \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   FME                            ///////
 \\\\\\\                      * @package    FME_Layaway                    \\\\\\\
 ///////    * @author     Malik Tahir Mehmood <malik.tahir786@gmail.com>   ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\* @copyright  Copyright 2010 © free-magentoextensions.com All right reserved\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

class FME_Layaway_Model_Invoice_Layawayfee extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $layawayfee=0;$baselayawayfee=0;
        $order = $invoice->getOrder();
	if ($order->getIsLayaway() && $order->getLayawayOrder()==0) {
	    $orderitems = $order->getAllVisibleItems();
	    
	    $invoiceitems = $invoice->getAllItems();
	    if (count($invoiceitems) > 1 && Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==0) {
		return $this; //to stop if more than one product in cart
	    }
	    $Itemids = array();
	    foreach($orderitems as $item){
		if($item->getIsLayaway()){
		    $Itemids[$item->getProductId()]['current'] =  $item->getLayawayFee();
		    $Itemids[$item->getProductId()]['base'] =  $item->getBaseLayawayFee();
		    $Itemids[$item->getProductId()]['qty'] =  $item->getQty();
		}
	    }
	    if (count($Itemids)==0) {
		return $this; //to stop if no layaway item
	    }
	    
	    if( Mage::helper('layaway')->getGlobalStoredDatafor('charges')){
		$lay_info = Zend_Json::decode($invoice->getLayawayInfo());
		if(!isset($lay_info['is_layaway_fee'])){
		    foreach($invoiceitems as $item){
			//if($item->getIsLayaway() && in_array($item->getProductId(),$Itemids)){
			if(isset($Itemids[$item->getProductId()]) && $item->getQty() > 0){
			    $layawayfee = $order->getLayawayFee();
			    $baselayawayfee = $order->getBaseLayawayFee();
			    $lay_info['is_layaway_fee']=1;
			    $lay_info = Zend_Json::encode($lay_info);
			    $invoice->setLayawayInfo($lay_info);
			    break;
			}
		    }
		}
	    }else{
		foreach($invoiceitems as $item){
		    //if($item->getIsLayaway() && in_array($item->getProductId(),$Itemids)){
		    if(isset($Itemids[$item->getProductId()]) && $item->getQty() > 0){
			    $data = $Itemids[$item->getProductId()];
			    $base = 0;$current =0;
			    if($item->getQty()<= $data['qty']){
				$base = $item->getQty()*($data['base']/$data['qty']);
				$current = $item->getQty()*($data['current']/$data['qty']);
			    }elseif($item->getQty() > $data['qty']){
				$base = $data['base'];
				$current =$data['current'];
			    }
			    $current = round($current,2);
			    $base = round($base,2);
			    $item->setData('layaway_fee',$current);
			    $item->setData('base_layaway_fee',$base);
			    //$item->save();
			    $layawayfee += $current;
			    $baselayawayfee += $base;
		    }
		}
		$invoice->setIsLayaway(1);
	    }
	    $layawayfee = round($layawayfee,2);
	    $baselayawayfee = round($baselayawayfee,2);
	    $invoice->setLayawayFee($layawayfee);
	    $invoice->setBaseLayawayFee($baselayawayfee);
	    $invoice->setGrandTotal($invoice->getGrandTotal() + $layawayfee);
	    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baselayawayfee);
        }
        return $this;
    }
    
}
