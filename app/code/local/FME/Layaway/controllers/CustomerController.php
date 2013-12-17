<?php 
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\  FME Layaway extension  \\\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   FME                            ///////
 \\\\\\\                      * @package    FME_Layaway              \\\\\\\
 ///////    * @author     Malik Tahir Mehmood <malik.tahir786@gmail.com>   ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\* @copyright  Copyright 2010 © free-magentoextensions.com All right reserved\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

class FME_Layaway_CustomerController extends Mage_Core_Controller_Front_Action
{
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
     public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

       if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'index') {
                $this->_getSession()->setBeforeAuthUrl(Mage::getUrl('*/*/index', array(
                    '_current' => true
                )));
            }
        }

        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('%1s Orders',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
        $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->renderLayout();
    }
    public function orderAction()
    {
	if (!$this->_loadValidOrder()) {
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('%1s Order # %1d Detail',Mage::helper('layaway')->getLabelsStoredDatafor('layaway'),Mage::registry('current_order')->getRealOrderId()));
        $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if($navigationBlock){
            $navigationBlock->setActive('layaway/customer');
        }
        $this->renderLayout();
    }
     protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            return true;
        } else {
            $this->_redirect('*/*/index');
        }
        return false;
    }
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && $order->getIsLayaway() && !$order->getLayawayOrder()
            ) {
            return true;
        }
        return false;
    }
    public function postDataAction()
    {
        $data = $this->getRequest()->getPost();
	$cart = Mage::getSingleton('checkout/cart');
	$cartweight = count($cart->getQuote()->getAllItems());
	$product = Mage::getModel('catalog/product');
            $pid = $product->getIdBySku('layaway_installment');
	    $websites = Mage::getModel('core/website')->getCollection()->getAllIds();
	    if($pid > 0){
		$data['product'] = $pid;
		$product = $product->load($pid);
	    }
	    else{
		$product->setSku("layaway_installment");
		$product->setAttributeSetId(4);
		$product->setTypeId('virtual');
		$product->setName('Installment');
		$product->setWebsiteIds(array(0=>1)); 
		$product->setDescription("Please don't delete or update this product.");
		$product->setShortDescription("Please don't delete or update this product.");
		$product->setUrlKey("please_dont_access_this_product");
		$product->setPrice(0.00); 
//		if some required custom attributes then enter any one of it here

		//Default Magento attribute
		$product->setWeight(0.0000);
		$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
		$product->setStatus(1);
		$product->setTaxClassId(0); # My default tax class
		$product->setStockData(array(
		    'manage_stock' => 0,
		    'use_config_manage_stock' => 0
		));
		$product->setCreatedAt(strtotime('now'));
		try {
		    $product->save();
		    $data['product'] = $product->getId();
		}
		catch (Exception $ex) {
		    Mage::getSingleton('customer/session')->addNotice('You can\'t proceed, Please report this issue to us.');
		    $this->_redirect('layaway/customer/order/',array('order_id' =>$data['order']));
		    return;
		}
	    }
	if(isset($data['order']) && $data['order']>0){
	    
	    
	    if($cartweight){
		Mage::getSingleton('customer/session')->addNotice('Please empty your cart first.');
		$this->_redirect('layaway/customer/order/',array('order_id' =>$data['order']));
		return;
	    }
	    $order = Mage::getModel('sales/order')->load($data['order']);
	    $currentcurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
	    if($order->getBaseCurrencyCode() != $currentcurrency && $order->getOrderCurrencyCode() != $currentcurrency){
		$appcurrency = $order->getBaseCurrencyCode() == $order->getOrderCurrencyCode()?$order->getBaseCurrencyCode():$order->getBaseCurrencyCode() ." or ". $order->getOrderCurrencyCode();
		Mage::getSingleton('customer/session')->addNotice(Mage::helper('layaway')->__('Please first select the appropriate currency  %s',$appcurrency));
		$this->_redirect('layaway/customer/order/',array('order_id' =>$data['order']));
		return;
	    }
	    $tobepaid = $order->getBaseCurrencyCode() == $currentcurrency?$order->getBaseLayawayRemaining():$order->getLayawayRemaining();
	    if($tobepaid < $data['amount']){
		Mage::getSingleton('customer/session')->addNotice(Mage::helper('layaway')->__('Your entered amount is exceeding the outstanding amount.'));
		$this->_redirect('layaway/customer/order/',array('order_id' =>$data['order']));
		return;
	    }
	    //$dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
	    $currentTimestamp = Mage::getModel('core/date')->timestamp();
	    if(isset($data['lastdate']) && $data['lastdate'] <= $currentTimestamp && $tobepaid > $data['amount']){
		Mage::getSingleton('customer/session')->addNotice(Mage::helper('layaway')->__('You have reached the maximum time limit, please pay full outstanding amount in %s now.',$currentcurrency));
		$this->_redirect('layaway/customer/order/',array('order_id' =>$data['order']));
		return;
	    }
	    if(isset($data['remaining_installments']) && $data['remaining_installments']<2 && $tobepaid > $data['amount']){
		Mage::getSingleton('customer/session')->addNotice(Mage::helper('layaway')->__('You have reached the maximum installments limit, please pay full outstanding amount in %s now.',$currentcurrency));
		$this->_redirect('layaway/customer/order/',array('order_id' =>$data['order']));
		return;
	    }
	    $data['qty']=1;
	    Mage::getSingleton('checkout/session')->unsLayawayProductName();
	    Mage::getSingleton('checkout/session')->setLayawayProductName(Mage::helper('layaway')->getLabelsStoredDatafor('installment') . ' #'. $data['installment'] .' for Order #' .$data['order_increment']);
	    $carturl = Mage::helper('checkout/cart')->getAddUrl($product, array('_query'=>http_build_query($data)));
	    Mage::app()->getResponse()->setRedirect($carturl);
	    
	    return $this;
	}
	$this->_redirect('*/*/index');
	return;
	
    }
}
