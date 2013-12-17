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

class FME_Layaway_Model_Quote_Layawayremaining extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    protected $_code = 'layaway_remaining';
       
	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
	    parent::collect($address);
	    try{
		if(Mage::helper('layaway')->getStoredDatafor('enable')){
		    $this->_setAmount(0);
		    $this->_setBaseAmount(0);
		    $items = $this->_getAddressItems($address);
		    if (!count($items)) {
			    return $this; //to eliminate the billing address
		    }
		    $quote = $address->getQuote();
		    $visibleitems = $quote->getAllVisibleItems();
		    if (count($visibleitems) > 1 && Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==0) {
			    return $this; //to stop if more than one product in cart
		    }
		    if($quote->getIsLayaway()){
			$go = true;$layawayremaining=0;
			foreach($items as $item){
			    if($item->getIsLayaway()){
				 $data = Mage::helper('layaway')->getLayawayProductData($item->getProductId());
			    //    if(!Mage::helper('layaway')->IsEnabled($data) || !$item->getIsLayaway()){
			    //	$go = false;
			    //    }else
				if(!$item->getParentItemId()){
				    
				    if($item->getBasePrice()){
					$_price =  $item->getBasePrice() - $item->getDiscountAmount();
					//$itemlayawayremaining = ($item->getBasePrice() - ($item->getBasePrice() * Mage::helper('layaway')->getLayawayFee('layaway_first',$data)/100))* $item->getQty();
				    }else{
					$_price =  $item->getPrice() - $item->getDiscountAmount();
					//$itemlayawayremaining = ($item->getPrice() - ($item->getPrice() * Mage::helper('layaway')->getLayawayFee('layaway_first',$data)/100))* $item->getQty();
				    }
				    $itemlayawayremaining = ($_price - ($_price * Mage::helper('layaway')->getLayawayFee('layaway_first',$data)/100))* $item->getQty();
				   
				    $itemlayawayremaining = round($itemlayawayremaining,2);
				    $itemcurrentcurrency = Mage::app()->getStore()->convertPrice($itemlayawayremaining);
				    $itemcurrentcurrency = round($itemcurrentcurrency,2);
				    $item->setData('layaway_remaining',$itemcurrentcurrency);
				    $item->setData('base_layaway_remaining',$itemlayawayremaining);
				    $item->save();
				    $layawayremaining += $itemlayawayremaining;
				    //echo $item->getBasePrice();
				}
			    }
			   
			}
			//if($go){
			    
			   // $session = Mage::getSingleton('checkout/session');
			    $layawayremaining = round($layawayremaining,2);
			    $currentcurrency = Mage::app()->getStore()->convertPrice($layawayremaining);
			    $currentcurrency = round($currentcurrency,2);
			    //$quote1 = $session->getQuote();
			    //$quote1->setData('layaway_remaining',$currentcurrency);
			    //$quote1->setData('base_layaway_remaining',$layawayremaining);
			    //$quote1->save();
			    $address->setLayawayRemaining($currentcurrency);
			    $address->setBaseLayawayRemaining($layawayremaining);
			    $quote->setLayawayRemaining($currentcurrency);
			    $quote->setBaseLayawayRemaining($layawayremaining);
			    $address->setIsLayaway(true);
			    $quote->setIsLayaway(true);
			    $address->setGrandTotal($address->getGrandTotal() - $address->getLayawayRemaining());
			    $address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getBaseLayawayRemaining());
			//}
		    }
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
	    try{
		$quote = $address->getQuote();
		if($quote->getIsLayaway() && Mage::helper('layaway')->getStoredDatafor('enable')){
		    $items = $this->_getAddressItems($address);
		    if (!count($items)) {
			    return $this; //to eliminate the billing address
		    }
		    $items = $quote->getAllVisibleItems();
		    if (count($items) > 1 && Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==0) {
			    return $this; // stop if more than one product in cart
		    }
		    
		    $address->addTotal(array(
			    'code'=>$this->getCode(),
			    'title' => Mage::helper('layaway')->getLabelsStoredDatafor('remaining'),
			    'value'=> -($address->getLayawayRemaining())
		    ));
		    return $this;
		    
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
}
