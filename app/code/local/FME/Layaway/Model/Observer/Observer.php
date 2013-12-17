<?php

class FME_Layaway_Model_Observer_Observer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the layaway_id refers to the key field in your database table.
        $this->_init('layaway/layaway', 'layaway_id');
    }
    
    public function OnRemoveCart($observer)
    {
	try{
	    $request= $this->_getRequest()->getParams();
	    $quote = $this->getQuote();
	    $items = $quote->getAllVisibleItems();
	    
	    if(Mage::helper('layaway')->getStoredDatafor('enable') && $quote->getIsLayaway()){
		if(count($items)>1){
		    $iswithlayaway = false;
		    foreach($items as $item){
			if(isset($request['id']) && $item->getId() != $request['id'] && $item->getIsLayaway()){
			    $iswithlayaway = true;
			}
		    }
		    if(!$iswithlayaway){
			$quote->setData('is_layaway',0);
			$quote->setData('layaway_order',0);
			$quote->setData('layaway_fee',0.0);
			$quote->setData('base_layaway_fee',0.0);
			$quote->setData('layaway_remaining',0.0);
			$quote->setData('base_layaway_remaining',0.0);
			$quote->save();
		    }
		}
	    }
	    if($quote->getLayawayOrder() > 0){
		$quote->setData('layaway_order',0);
		Mage::getSingleton('checkout/session')->unsLayawayProductName();
		$quote->save();
	    }
	    
	}catch (Exception $e) {
	    throw $e;
	}
    }
    public function OnConfigureCart($observer)
    {
	try{
	    if(Mage::helper('layaway')->getStoredDatafor('enable')){
		$request= $this->_getRequest()->getPost();
		$quote = $this->getQuote();
		$iswithlayaway = isset($request['options']['layaway']) && $request['options']['layaway']==1;
		if($iswithlayaway){
		    $quote->setData('is_layaway',1);
		    $quote->setData('layaway_order',0);
		    $items = $quote->getAllVisibleItems();
		    if(count($items)){
			foreach($items as $item){
			    if($item->getProductId() == $request['product']){
				$item->setData('is_layaway',1);
			    }
			}
		    }
		    $quote->save();
		}else{
		    $items = $quote->getAllVisibleItems();
		    $iswithlayaway = false;
		    foreach($items as $item){
			if($item->getIsLayaway()){
			    $iswithlayaway = true;
			}
		    }
		    if(!$iswithlayaway){
			$quote->setData('is_layaway',0);
			$quote->setData('layaway_order',0);
			$quote->setData('layaway_fee',0.0);
			$quote->setData('base_layaway_fee',0.0);
			$quote->setData('layaway_remaining',0.0);
			$quote->setData('base_layaway_remaining',0.0);
			$quote->save();
		    }
		}
	    }
	}
	catch (Exception $e) {
	    throw $e;
	}
	
    }
    public function OnUpdateCart($observer)
    {
	try{
	    if(Mage::helper('layaway')->getStoredDatafor('enable')){
		$data= $this->_getRequest()->getPost('cart');
		$quote = $this->getQuote();
		$items = $quote->getAllVisibleItems();
		if($quote->getLayawayOrder() > 0){
		    if(count($items)){
			foreach($items as $item){
			    if($data[$item->getId()]['qty']>1){
				$item->setQty(1);
				Mage::getSingleton('checkout/session')->addError(Mage::helper('layaway')->__('You Should not update installment from cart, this may charge you incorrect amount.'));
			    }
			}
		    }
		    $quote->save();
		}
		
	    }
	}
	catch (Exception $e) {
	    throw $e;
	}
	
    }
    public function getQuote()
    {
	$session = Mage::getSingleton('checkout/session');
	return $session->getQuote();
    }
    public function validateLoginStatus()
    {
	$request = $this->_getRequest()->getParams();
	$iswithlayaway = isset($request['options']['layaway']) && $request['options']['layaway']==1;
	$session = Mage::getSingleton('checkout/session');
	$customersession = Mage::getSingleton('customer/session');
	if(!$customersession->isLoggedIn() && $iswithlayaway ==1){
	    Mage::throwException(Mage::helper('layaway')->__('Please login or register before purchasing the product on %1s.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
	    $session->setRedirectUrl(Mage::getUrl('customer/login'));
	    if($session->getUseNotice() === null) {
		    $session->setUseNotice(true);
		}
		
		return;
	}
    }
    public function AddToCartAfter($observer)
    {
	try{
	    
	    if(Mage::helper('layaway')->getStoredDatafor('enable')){
		$session = Mage::getSingleton('checkout/session');
		$customersession = Mage::getSingleton('customer/session');
		$quote = $this->getQuote();$items = $quote->getAllVisibleItems();
		if($quote->getLayawayOrder() > 0 && count($items)>1){
		    Mage::throwException(Mage::helper('layaway')->__('Please finish with the selected %1s First.',Mage::helper('layaway')->getLabelsStoredDatafor('installment')));
		    return;
		}
		$request = $this->_getRequest()->getParams();
		if(!isset($request['product']) && Mage::getSingleton('checkout/session')->getDoNotCheckout()){
		    Mage::getSingleton('checkout/cart')->removeItem(Mage::getSingleton('checkout/session')->getDoNotCheckout())->save();
		    Mage::getSingleton('checkout/session')->unsDoNotCheckout();
		    Mage::throwException(Mage::helper('layaway')->__('Sorry, you cannot proceed.'));
		    return;
		}
		$layaway_status = isset($request['product'])?Mage::helper('layaway')->IsLayawayEnabled($request['product']):false;
		//$layaway_status = Mage::helper('layaway')->getGlobalStoredDatafor('multiple')==3 && $layaway_status==true?true:$layaway_status;
		$iswithlayaway = isset($request['options']['layaway']) && $request['options']['layaway']==1;
		$installment = isset($request['options']['layaway_order']) && $request['options']['layaway_order']>0;
		$multiple = Mage::helper('layaway')->getGlobalStoredDatafor('multiple');
		if(!$installment){
		    $product = Mage::getModel('catalog/product')->load($request['product']);
		    if($layaway_status != 0 && !isset($request['options']['layaway']) && $product->getPreorder()){
			
			$session->setRedirectUrl($product->getProductUrl());
			if($session->getUseNotice() === null) {
			    $session->setUseNotice(true);
			}
			if(!$customersession->isLoggedIn()){
			    Mage::getSingleton('customer/session')->addNotice(Mage::helper('layaway')->__('Registered customers can only purchase the product on %1s.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
			}
			Mage::throwException(Mage::helper('layaway')->__('Do you want to checkout with %1s.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
			return;
		    }
		    elseif(!$product->getPreorder()){
			$iswithlayaway = 0;
		    }
		    if(!$customersession->isLoggedIn() && $iswithlayaway ==1){
			
			$product = Mage::getModel('catalog/product')->load($request['product']);
			$customersession->setBeforeAuthUrl(Mage::getUrl('checkout/cart/add',array('_query'=>http_build_query($request))));
			$session->setRedirectUrl(Mage::getUrl('customer/account/login'));
			if($session->getUseNotice() === null) {
				$session->setUseNotice(true);
			    }
			    Mage::throwException(Mage::helper('layaway')->__('Only registered customers can buy on %1s.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
			    return;exit;
		    }
		    $bundle = isset($request['bundle_option']);
		    $configurable = isset($request['super_attribute']);
		    if($multiple!=2){
			if($iswithlayaway==1 && $quote->getIsLayaway()==true && count($items) > 1 && $multiple==0 ){
			    foreach($items as $item){
				if(isset($request['product']) && $item->getProductId() != $request['product']){
				    Mage::throwException(Mage::helper('layaway')->__('You can checkout with only one product on %1s at a time.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
				    return;
				}
			    }
			}
			elseif($iswithlayaway ==1 && !$quote->getIsLayaway() && count($items)){
			    if((count($items)>1 && !$bundle  && !$configurable && $multiple==0) || (count($items)>2 && $configurable && $multiple==0)){
				Mage::throwException(Mage::helper('layaway')->__('You can\'t checkout a %1s product with a non-%2s. Please either empty the cart or finish with the existing cart.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway'),Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
				return;
			    }
			    if(count($items)>1 && $multiple==0 && !$configurable){
				foreach($items as $item){
				    if(isset($request['qty']) && $item->getQty()!= $request['qty']){
					Mage::throwException(Mage::helper('layaway')->__('You can\'t checkout a %1s product with a non-%2s. Please either empty the cart or finish with the existing cart.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway'),Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
					return;
				    }
				}
			    }
			}elseif($iswithlayaway==0 && $quote->getIsLayaway() && $multiple!=2 && !$installment){
			    Mage::throwException(Mage::helper('layaway')->__('You can\'t checkout a %1s product with a non-%2s. Please either empty the cart or finish with the existing cart.',Mage::helper('layaway')->getLabelsStoredDatafor('layaway'),Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
			    return;
			}
		    }
		    if($layaway_status!=0 && $iswithlayaway ==1 && (!$quote->getIsLayaway() || $multiple) && (count($items)==1 || $multiple || $bundle || $configurable)){
			$quote->setData('is_layaway',1);
			$items = $quote->getAllVisibleItems();$layawayfee=0;$layawayremaining=0;
			if(count($items)){
			    foreach($items as $item){
				if($item->getProductId() == $request['product']){
				    $data = Mage::helper('layaway')->getLayawayProductData($item->getProductId());
				    $item->setData('is_layaway',1);
				    $layawayfee += Mage::helper('layaway')->getLayawayFee('layaway_fees',$data) * $item->getQty();
				    $layawayremaining += ($item->getPrice() - ($item->getPrice() * Mage::helper('layaway')->getLayawayFee('layaway_first',$data)/100))* $item->getQty();
				}
			    }
			    
			}
			$quote->save();
		    }
		}elseif($installment){
		    $quote->setData('layaway_order',$request['options']['layaway_order']);
		    if(isset($request['layaway_info'])){
			$lay_info = Zend_Json::encode($request['layaway_info']);
			$quote->setData('layaway_info',$lay_info);
		    }
		    $quote->save();
		}
	    }
	}
	catch (Exception $e) {
	    throw $e;
	}
    }
	
	protected function _getRequest()
	{
		return Mage::app()->getRequest();
	}
	public function modifywithLayaway($event){
	    try{
		$version = str_replace('.','',Mage::getVersion());
		if(Mage::helper('layaway')->getStoredDatafor('enable') && $version >= 1420){
		    $paypalcart = $event->getPaypalCart();
		    if($paypalcart->getSalesEntity()->getIsLayaway() && $paypalcart->getSalesEntity()->getPayment()->getMethod() =='paypal_standard'){
			$paypalcart->addItem(Mage::helper('layaway')->getLabelsStoredDatafor('fees'), 1, 1.00 * $paypalcart->getSalesEntity()->getLayawayFee(),'');
			//$paypalcart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL,$paypalcart->getSalesEntity()->getLayawayFee());
			$paypalcart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,$paypalcart->getSalesEntity()->getLayawayRemaining());
			
		    }
		    elseif($paypalcart->getSalesEntity()->getIsLayaway() && $paypalcart->getSalesEntity()->getPayment()->getMethod() !='paypal_standard'){
			$paypalcart->addItem(Mage::helper('layaway')->getLabelsStoredDatafor('fees'), 1, 1.00 * $paypalcart->getSalesEntity()->getLayawayFee(),'');
			$paypalcart->addItem(Mage::helper('layaway')->getLabelsStoredDatafor('remaining'), 1, -1.00 * $paypalcart->getSalesEntity()->getLayawayRemaining(),'');   
		    } 
		    
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
	public function updatePrice($observer) {
	    try{
		$params = $this->_getRequest()->getParams();
		$event = $observer->getEvent();
		$quote_item = $event->getQuoteItem();
		Mage::getSingleton('checkout/session')->setDoNotCheckout(false);
		if($quote_item->getSku() =='layaway_installment'){
		    $new_price = isset($params['amount'])?$params['amount']:0;
		    if($new_price<=0){
			Mage::getSingleton('checkout/session')->setDoNotCheckout($quote_item->getProductId());
			return;
		    }
		    $quote_item->setOriginalCustomPrice($new_price);
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
	public function updateOrder($observer) {
	    try{
		$event = $observer->getEvent();
		$quote = $event->getQuote();
		$order = $event->getOrder(); 
		if($quote->getLayawayOrder() && $quote->getLayawayOrder()>0){
		    $order->setLayawayOrder($quote->getLayawayOrder());
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
	public function UpdateInstallments($observer) {
	    try{
		$order = new Mage_Sales_Model_Order();
		$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($incrementId);
		if($order->getLayawayOrder() && $order->getLayawayOrder()>0){
		    
		    foreach($order->getAllVisibleItems() as $item)
		    {
			if($item->getSku()=='layaway_installment' && Mage::getSingleton('checkout/session')->getLayawayProductName()){
			    $item->setName(Mage::getSingleton('checkout/session')->getLayawayProductName());
			}
		    }
		     $order->save();
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
	public function CartSavebefore(Varien_Event_Observer $observer)
	{
	    $cart = $observer->getCart();
	    $items = $cart->getItems();
	
	    foreach($items as $item) {
		if($item->getSku()=='layaway_installment' && Mage::getSingleton('checkout/session')->getLayawayProductName()){
		    $item->setName(Mage::getSingleton('checkout/session')->getLayawayProductName());
		}
		    
	    }
	}
	public function validateInstallment($observer)
	{
	    $quoteItem = $observer->getEvent()->getItem();
	    /* @var $quoteItem Mage_Sales_Model_Quote_Item */
	    if ($quoteItem->getSku() =='layaway_installment') {
		if($quoteItem->getQty()>1 && $quoteItem->getPrice()>0){
		    $quoteItem->setQty(1);
		    return $quoteItem;
		}else if(($quoteItem->getQty()>=1 && $quoteItem->getPrice()<=0) || $quoteItem->getName() == 'Installment'){
		    Mage::getSingleton('checkout/cart')->removeItem($quoteItem->getId())->save();
		}
	    }
	}
	public function updateInstallment($observer)
	{
	    $product = $observer->getProduct();
	    $quoteItem = $observer->getQuoteItem();
	    if ($quoteItem->getSku() =='layaway_installment') {
		if(Mage::getSingleton('checkout/session')->getLayawayProductName()){
		    $product->setName(Mage::getSingleton('checkout/session')->getLayawayProductName());
		    $quoteItem->setName(Mage::getSingleton('checkout/session')->getLayawayProductName());
		}
	    }
	}
	
	public function creditmemoSaveAfter($observer)
	{
	    $creditmemo = $observer->getEvent()->getCreditmemo();
	    if($creditmemo->getId()){
		$creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemo->getId());
		$order = $creditmemo->getOrder();
		$items = $creditmemo->getAllItems();
		if($order->getIsLayaway()){
		    $order->setBaseGrandTotal($order->getBaseGrandTotal() - $order->getBaseLayawayFee());
		    $order->setGrandTotal($order->getGrandTotal() - $order->getLayawayFee());
		    $order->save();
		}
		elseif(count($items)==1){
		    foreach($items as $item){
			if($item->getSku() =='layaway_installment'){
			    $remaining = 0;$baseremaining=0;$amount = 0;$baseamount=0;
			    //Mage::getModel('sales/order')->load($creditmemo->getOrderId());
			    $total = $creditmemo->getGrandTotal();
			    if($order->getLayawayOrder() >0){
				$main_order = Mage::getModel('sales/order')->load($order->getLayawayOrder());
				$rate = $main_order->getStoreToOrderRate();
				if($order->getOrderCurrencyCode() == $main_order->getOrderCurrencyCode() ){
				    $amount = round($total,2);
				    //$baseamount = round(($amount/$rate),2);
				}elseif($order->getOrderCurrencyCode() != $main_order->getOrderCurrencyCode()){
				    $amount = round(($rate * $total),2);
				    //$baseamount = round($amount,2);
				}
				if($order->getOrderCurrencyCode() == $main_order->getBaseCurrencyCode() ){
				    //$amount = round($amount,2);
				   $baseamount = round($total,2);
				}elseif($order->getOrderCurrencyCode()!= $main_order->getBaseCurrencyCode()){
				   // $amount = round(($rate * $amount),2);
				     $baseamount = round(($total/$rate),2);
				}
				$remaining =$main_order->getLayawayRemaining() + $amount;
				$baseremaining = $main_order->getBaseLayawayRemaining() + $baseamount;
				$remaining = $remaining < 0?0.00:$remaining;
				$baseremaining = $baseremaining < 0?0.00:$baseremaining;
				$main_order->setBaseLayawayRemaining($baseremaining);$main_order->setLayawayRemaining($remaining);
				$main_order->setBaseGrandTotal($main_order->getBaseGrandTotal() - $baseamount);$main_order->setGrandTotal($main_order->getGrandTotal() - $amount);
				$main_order->save();
				
				$info = Zend_Json::decode($order->getLayawayInfo());
				if(isset($info['items'])){
				    $order_items = $main_order->getAllItems();
				    foreach($order_items as $order_item){
					if(in_array($order_item->getId(),$info['items'])){
					    $itembaseamount = $order_item->getBaseLayawayRemaining() +  $baseamount;
					    $itemamount =  $order_item->getLayawayRemaining()+ $amount;
					    $itemremaining = $itemamount < 0?0.00:$itemamount;
					    $itembaseremaining = $itembaseamount < 0?0.00:$itembaseamount;
					    $order_item->setLayawayRemaining($itemremaining);
					    $order_item->setBaseLayawayRemaining($itembaseremaining);
					    $order_item->save();
					    $info['paid'][$order_item->getId()]['amount']=isset($info['paid'][$order_item->getId()]['amount']) && $info['paid'][$order_item->getId()]['amount'] >= $amount?$info['paid'][$order_item->getId()]['amount']-$amount:0;
					    $info['paid'][$order_item->getId()]['base']=isset($info['paid'][$order_item->getId()]['base']) && $info['paid'][$order_item->getId()]['amount'] >= $baseamount?$info['paid'][$order_item->getId()]['base']-$baseamount:0;
					    $info = Zend_Json::encode($info);
					    $main_order->setLayawayInfo($info);
					    $main_order->save();
					}
				    }
				}
			    }
			}
		    }
		}
	    }
	}
	public function invoiceSaveAfter($observer)
	{
	    $invoice = $observer->getEvent()->getInvoice();
	    if($invoice->getId()){
		$invoice = Mage::getModel('sales/order_invoice')->load($invoice->getId());
		$items = $invoice->getAllItems();
		if(count($items)==1){
		    foreach($items as $item){
			if($item->getSku() =='layaway_installment'){
			    $remaining = 0;$baseremaining=0;$amount = 0;$baseamount=0;
			    $order = Mage::getModel('sales/order')->load($invoice->getOrderId());
			    $info = $order->getLayawayInfo();
			    $total = $invoice->getGrandTotal();
			    if($order->getLayawayOrder() >0){
				$main_order = Mage::getModel('sales/order')->load($order->getLayawayOrder());
				$rate = $main_order->getStoreToOrderRate();
				if($order->getOrderCurrencyCode() == $main_order->getOrderCurrencyCode() ){
				    $amount = round($total,2);
				    //$baseamount = round(($amount/$rate),2);
				}elseif($order->getOrderCurrencyCode() != $main_order->getOrderCurrencyCode()){
				    $amount = round(($rate * $total),2);
				    //$baseamount = round($amount,2);
				}
				if($order->getOrderCurrencyCode() == $main_order->getBaseCurrencyCode() ){
				    //$amount = round($amount,2);
				   $baseamount = round($total,2);
				}elseif($order->getOrderCurrencyCode()!= $main_order->getBaseCurrencyCode()){
				   // $amount = round(($rate * $amount),2);
				    $baseamount = round(($total/$rate),2);
				}
				$remaining =$main_order->getLayawayRemaining() - $amount;
				$baseremaining = $main_order->getBaseLayawayRemaining() - $baseamount;
				$remaining = $remaining < 0?0.00:$remaining;
				$baseremaining = $baseremaining < 0?0.00:$baseremaining;
				$main_order->setBaseLayawayRemaining($baseremaining);$main_order->setLayawayRemaining($remaining);
				$main_order->setBaseGrandTotal($baseamount + $main_order->getBaseGrandTotal());$main_order->setGrandTotal($amount + $main_order->getGrandTotal());
				$basetotaldue=$main_order->getBaseTotalDue() - $baseamount;
				$basetotaldue=$basetotaldue<0?0:$basetotaldue;
				$totaldue=$main_order->getTotalDue() - $amount;
				$totaldue=$totaldue<0?0:$totaldue;
				$basetotalpaid =$main_order->getBaseTotalPaid() + $baseamount;
				$totalpaid = $main_order->getTotalPaid() + $amount;
				$main_order->setBaseTotalDue($basetotaldue);$main_order->setTotalDue($totaldue);
				$main_order->setBaseTotalPaid($basetotalpaid);$main_order->setTotalPaid($totalpaid);
				if($remaining == 0){
				    $status = Mage::helper('layaway')->getStoredDatafor('processing_status');
				    $status = $status!=''?$status:'layaway_complete';
				    $main_order->setStatus($status);
				}
				$main_order->save();
				$info = Zend_Json::decode($order->getLayawayInfo());
				if(isset($info['items'])){
				    $order_items = $main_order->getAllItems();
				    foreach($order_items as $order_item){
					if(in_array($order_item->getId(),$info['items'])){
					    $itembaseamount =$order_item->getBaseLayawayRemaining() -  $baseamount;
					    $itemamount =  $order_item->getLayawayRemaining()- $amount;
					    $itemremaining = $itemamount < 0?0.00:$itemamount;
					    $itembaseremaining = $itembaseamount < 0?0.00:$itembaseamount;
					    $order_item->setLayawayRemaining($itemremaining);
					    $order_item->setBaseLayawayRemaining($itembaseremaining);
					    $order_item->save();
					    $info['paid'][$order_item->getId()]['amount']=isset($info['paid'][$order_item->getId()]['amount'])?$info['paid'][$order_item->getId()]['amount']+$amount:$amount;
					    $info['paid'][$order_item->getId()]['base']=isset($info['paid'][$order_item->getId()]['base'])?$info['paid'][$order_item->getId()]['base']+$baseamount:$baseamount;
					    $info = Zend_Json::encode($info);
					    $main_order->setLayawayInfo($info);
					    $main_order->save();
					}
				    }
				}
			    }
			}
		    }
		}
	    }
	}
	public function validateStatus($observer)
	{
	    $order = $observer->getEvent()->getOrder();
	    if($order->getIsLayaway()){
		$remaining = $order->getLayawayRemaining();
		
		if($remaining > 0 && ($order->getStatus()=='complete' || $order->getStatus()=='processing')){
		    $status = Mage::helper('layaway')->getStoredDatafor('pending_status');
		    $status = $status!=''?$status:'layaway_pending';
		    $order->setStatus($status);
		    $order->save();
		}
	    }
	    
	}
    }
