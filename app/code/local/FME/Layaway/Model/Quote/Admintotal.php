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

class FME_Layaway_Model_Quote_Admintotal extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    protected $_code = 'layaway_fee';
       
	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		parent::collect($address);

		$this->_setAmount(0);
		$this->_setBaseAmount(0);

		$items = $this->_getAddressItems($address);
		if (!count($items)) {
			return $this; //to eliminate the billing address
		}
                if (count($items) > 1 && Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==0) {
			return $this; //to stop if more than one product in cart
		}


		$quote = $address->getQuote();
                if($quote->getIsLayaway()){
                    $go = true;$layawayfee = 0;
                    foreach($items as $item){
			if($item->getIsLayaway()){
			    $data = Mage::helper('layaway')->getLayawayProductData($item->getProductId());
			    //if(!Mage::helper('layaway')->IsEnabled($data)){
			    //    $go = false;
			    //}else{
				$layawayfee += Mage::helper('layaway')->getLayawayFee('layaway_fees',$data) * $item->getQty();
			    //}
			}
                    }
                   
                    //if($go){
//			$currentcurrency = Mage::app()->getStore()->convertPrice($layawayfee);
//                        $session = Mage::getSingleton('checkout/session');
//                        $quote1 = $session->getQuote();
//                        $quote1->setData('layaway_fee',$currentcurrency);
//                        $quote1->setData('base_layaway_fee',$layawayfee);
//                        $quote1->save();
//                        $address->setLayawayFee($currentcurrency);
//                        $address->setBaseLayawayFee($layawayfee);
//                        $quote->setLayawayFee($currentcurrency);
//                        
//                        $address->setGrandTotal($address->getGrandTotal() + $address->getLayawayFee());
//                        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseLayawayFee());
			    $layawayfee = round($layawayfee,2);
			    $currentcurrency = Mage::app()->getStore()->convertPrice($layawayfee);
			    $currentcurrency = round($currentcurrency,2);
			    $address->setLayawayFee($currentcurrency);
			    $address->setBaseLayawayFee($layawayfee);
			    $address->setIsLayaway(true);
			    $quote->setLayawayFee($currentcurrency);
			    $quote->setBaseLayawayFee($layawayfee);
			    $quote->setIsLayaway(true);
			    $address->setGrandTotal($address->getGrandTotal() + $address->getLayawayFee());
			    $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseLayawayFee());
                    //}
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
			    'title' => Mage::helper('layaway')->getLabelsStoredDatafor('fees'),
			    'value'=> $address->getLayawayFee()
		    ));
		    return $this;
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
    
}
