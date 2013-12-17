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

class FME_Layaway_Model_Quote_Layawayfee extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    protected $_code = 'layaway_fee';
       
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
			return $this; // stop if more than one product in cart
		}

		
		    //$quote = $address->getQuote();
		    if($quote->getIsLayaway()){
			$go = true;$layawayfee = 0;
			if( Mage::helper('layaway')->getGlobalStoredDatafor('charges')){
			    $layawayfee = Mage::helper('layaway')->getGlobalStoredDatafor('layaway_fees');
			}else{
			    foreach($items as $item){
				if($item->getIsLayaway()){
				    $data = Mage::helper('layaway')->getLayawayProductData($item->getProductId());
				    if(!$item->getParentItemId()){
					$itemlayawayfee = Mage::helper('layaway')->getLayawayFee('layaway_fees',$data) * $item->getQty();
					$itemlayawayfee = round($itemlayawayfee,2);
					$itemcurrentcurrency = Mage::app()->getStore()->convertPrice($itemlayawayfee);
					$itemcurrentcurrency = round($itemcurrentcurrency,2);
					$item->setData('layaway_fee',$itemcurrentcurrency);
					$item->setData('base_layaway_fee',$itemlayawayfee);
					$item->save();
					$layawayfee +=$itemlayawayfee;
				    }
				}
			    }
			}
			//if($go){
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
		    if(!count($items)) {
			    return $this; //to eliminate the billing address
		    }
		    $items = $quote->getAllVisibleItems();
		    if(count($items) > 1 && Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==0) {
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
