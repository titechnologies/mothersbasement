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

class FME_Layaway_Model_Invoice_Layawayremaining extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
	$layawayremaining=0;$baselayawayremaining=0;
        $order = $invoice->getOrder();
	if ($order->getIsLayaway() && $order->getLayawayOrder()==0) {
	   
	    $invoiceitems = $invoice->getAllItems();
	    
	    $orderitems = $order->getAllVisibleItems();
	    if (count($invoiceitems) > 1 && Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==0) {
		    return $this; //to stop if more than one product in cart
	    }
	    $Itemids = array();
	    foreach($orderitems as $item){
		if($item->getIsLayaway()){
		    $Itemids[$item->getProductId()]['current'] =  $item->getLayawayRemaining();
		    $Itemids[$item->getProductId()]['base'] =  $item->getBaseLayawayRemaining();
		    $Itemids[$item->getProductId()]['qty'] =  $item->getQty();
		}
	    }
	    if (count($Itemids) < 1) {
		    return $this; //to stop if no layaway item
	    }
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
			
			$item->setData('is_layaway',1);
			$item->setData('layaway_remaining',$current);
			$item->setData('base_layaway_remaining',$base);
			//$item->save();
			$layawayremaining += $current;
			$baselayawayremaining += $base;
		}
	    }
	    $invoice->setIsLayaway(1);
        }else{
	    $invoice->setIsLayaway(0);
	}
	$layawayremaining = round($layawayremaining,2);
	$baselayawayremaining = round($baselayawayremaining,2);
	$invoice->setLayawayRemaining($layawayremaining);
	$invoice->setBaseLayawayRemaining($baselayawayremaining);
	$invoice->setGrandTotal($invoice->getGrandTotal() - $layawayremaining);
	$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baselayawayremaining);
        return $this;
    }
    
}
