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

class FME_Layaway_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    function getheading(){
	return $this->getStoredDatafor('heading');
    }
    function getStoredDatafor($data){
	return Mage::getStoreConfig('layaway/general/' . $data);
    }
    function getGlobalStoredDatafor($data){
	return Mage::getStoreConfig('layaway/global/' . $data);
    }
    function getPayingOption($data = null){
	
	$allowed = explode(',', $this->getGlobalStoredDatafor('payoption'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[] = $k;
        }
	
	if($data!=null && in_array($data,$arr)){
	    return true;
	}elseif($data!=null){
	    return false;
	}
        
        return $arr;
    }
    function getLabelsStoredDatafor($data){
	return Mage::getStoreConfig('layaway/labels/' . $data);
    }
    function getCustomerLabel(){
	$label = $this->__('%1s Orders',$this->getLabelsStoredDatafor('layaway'));
	return $label;
    }
    function getTable($table)
    {
        if($table)return Mage::getSingleton('core/resource')->getTableName('layaway/' . $table);
       return;
    }
    public function IsLayawayEnabled($productid = null){
	if(Mage::helper('layaway')->getStoredDatafor('enable')){
	    try{
		if(Mage::registry('current_product')){
		    $productid = Mage::registry('current_product')->getId();
		}
		if(!$productid){
		    return 0;
		}
		$collection  = $this->getLayawayProductData($productid);
		$return = $this->IsEnabled($collection);
		return $return;
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
    }
    
    public function getLayawayProductData($id){
	return Mage::getModel('layaway/products')->getCollection()->addFieldToFilter('product_id',$id)->getData();//->addFieldToFilter('layaway_status',1)->getData();
    }
    
    public function IsEnabled($collection){
	if(Mage::helper('layaway')->getStoredDatafor('enable')){
	    try{
		$status = $this->getStoredDatafor('enable_all');
		if(count($collection) > 0){
		    if($collection[0]['config_layaway_status'] == 0){
			return $collection[0]['layaway_status'];
		    }elseif($collection[0]['config_layaway_status'] == 1){
			return $status;
		    }else{
			return 0;
		    }
		}else{
		    return $status;
		}
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
    }
    
    public function getLayawayDuration($collection, $global){
	$period = $this->getLayawayData('layaway_period' , $collection, $global);
	$frequency = $this->getLayawayData('layaway_period_frequency' , $collection, $global);
	$detail = Mage::getModel('layaway/period')->toOptionsArray();
	if($period){
	    return $frequency . " " . $detail[$period];
	}
	return $detail[0];
    }
    
    public function getLayawayTimeLeft($collection, $startdate , $lastdate = null, $global){
	
	$period = $this->getLayawayData('layaway_period' , $collection,$global);
	$frequency = $this->getLayawayData('layaway_period_frequency' , $collection,$global);
	$detail = Mage::getModel('layaway/period')->toOptionsArray();
	if($period){
	    $date = new Zend_Date($startdate);
	    if($period==1){
		$date->add($frequency, Zend_Date::DAY);
	    }
	    elseif($period==2){
		$date->add($frequency, Zend_Date::WEEK);
	    }
	    elseif($period==3){
		$date->add($frequency, Zend_Date::MONTH);
	    }
	    elseif($period==4){
		$date->add($frequency, Zend_Date::YEAR);
	    }
	    return $date->get();
	}
	return;
    }
    
    public function getOutstandingamount($order){
	$appcurrency = $order->getBaseCurrencyCode() == $order->getOrderCurrencyCode()?$order->formatPrice($order->getBaseLayawayRemaining()): $order->formatPrice($order->getLayawayRemaining()) ." or ". Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())->getSymbol() . $order->getBaseLayawayRemaining();
		
	return $appcurrency;
    }
    
    public function getOrderProduct($order){
	$items = $order->getAllVisibleItems();
	if(count($items)){
	    foreach($items as $item){
		return $item;
	    }
	}
	return;
    }
    public function getLayawayFee($field,$collection){
	
	$ret = $this->getLayawayData($field,$collection);
	//print_r($ret);if($field!='layaway_first')exit;
	return $ret;
    }
    
    public function getLayawayDetails($field , $id = 0){
	try{
	    if($id){
		$collection = $this->getLayawayProductData($id);
		return $this->getLayawayData($field,$collection);
	    }
	}
	catch (Exception $e) {
	    throw $e;
	}
    }
    
    public function getLayawayData($field,$collection,$global = false){
	if(Mage::helper('layaway')->getStoredDatafor('enable')){
	    try{
		if($field ==''){
		return 0;
		}
		$configfield = 'config_'.$field;
		if($this->IsEnabled($collection) && count($collection) > 0){
		    if($collection[0][$configfield] == 1 || $global){
			return $this->getGlobalStoredDatafor($field);
		    }else if($collection[0][$configfield] == 0){
			return $collection[0][$field];
		    }else{
			return 0;
		    }
		    
		}elseif($this->getStoredDatafor('enable_all')){
		    return $this->getGlobalStoredDatafor($field);
		}
		return 0;
	    }
	    catch (Exception $e) {
		throw $e;
	    }
	}
    }
    public function getApprovedInstallments($_order)
    {
        if($_order){
            $paid_orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('layaway_order',$_order)
		->addFieldToFilter('layaway_status','approved')
                ->getData();
            return count($paid_orders);
        }
        return 0;
    }
//    public function SaveandUpdate($data){
//	$Exists = array();
//	unset($Exists);
//	$_Write = Mage::getSingleton('core/resource')->getConnection('core_read');
//	
//	$tables   = $this->getTable('quote');
//
//	$allentries = $_Write->select()
//	    ->from($tables, '*')
//	    ->where('product_id=?', $data['product_id'])
//	    ->where('quote_id=?', $data['quote_id'])
//	    ->where('item_id=?', $data['item_id']);
//	$Exists = $_Write->fetchRow($allentries);
//	$qty = $Exists['layaway_selected'] + $data['qty'];
//	if(!empty($Exists) && !empty($Exists['layaway_id'])){
//	    $_Write->update( $tables,
//		array("layaway_selected" => $qty),
//		array("layaway_id =?" => $Exists['layaway_id'])
//	    );
//	}elseif(empty($Exists)) {
//	    $_Write->insert( $tables ,
//	    array(
//		"quote_id"	=> $data['quote_id'],
//		"item_id" 	=> $data['item_id'],
//		"product_id" 	=> $data['product_id'],
//		"layaway_selected" => $data['qty'],
//	    ));
//	}
//	return;
//    }
//    public function getLayawayData($id){
//	$Exists = array();
//	unset($Exists);
//	$_Write = Mage::getSingleton('core/resource')->getConnection('core_read');
//	
//	$tables   = $this->getTable('quote');
//
//	$allentries = $_Write->select()
//	    ->from($tables, '*')
//	    ->where('quote_id=?', $data['quote_id']);
//	$Exists = $_Write->fetchRow($allentries);
//	$qty = $Exists['layaway_selected'] + $data['qty'];
//	if(!empty($Exists) && !empty($Exists['layaway_id'])){
//	    $_Write->update( $tables,
//		array("layaway_selected" => $qty),
//		array("layaway_id =?" => $Exists['layaway_id'])
//	    );
//	}elseif(empty($Exists)) {
//	    $_Write->insert( $tables ,
//	    array(
//		"quote_id"	=> $data['quote_id'],
//		"item_id" 	=> $data['item_id'],
//		"product_id" 	=> $data['product_id'],
//		"layaway_selected" => $data['qty'],
//	    ));
//	}
//	return;
//    }
    public function getStoreMethods($code,$store = null)
    {
	try{
	    $pamenthelper = Mage::helper('payment');
        //$res = array();
        //foreach ($pamenthelper->getPaymentMethods($store) as $code => $methodConfig) {
            $prefix = 'payment/' . $code . '/';
            if (!$model = Mage::getStoreConfig($prefix . 'model', $store)) {
                continue;
            }
            $methodInstance = Mage::getModel($model);
            if (!$methodInstance) {
                continue;
            }
            $methodInstance->setStore($store);
            
            $sortOrder = (int)$methodInstance->getConfigData('sort_order', $store);
            $methodInstance->setSortOrder($sortOrder);
            return $methodInstance;
        //}
        //
        //usort($res, array($this, '_sortMethods'));
        //return $res;
	}
	catch (Exception $e) {
	    throw $e;
	}
    }
    public function getPayMethods()
    {
	try{
	    $payments = Mage::getSingleton('payment/config')->getActiveMethods();
	    $methods = array();
	    foreach ($payments as $paymentCode=>$paymentModel) {
		$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
		$methods[$paymentCode] = array(
		    'label'   => $paymentTitle,
		    'value' => $paymentCode
		);
	    }
	    return $methods;
	}
	catch (Exception $e) {
	    throw $e;
	}
    }
    public function getLayawayProduct()
    {
	try{
	    $product = Mage::getModel('catalog/product');
            $pid = $product->getIdBySku('layaway_installment');
	    if($pid > 0){return $pid;}
	    else{
		$product->setSku("layaway_installment");
		$product->setAttributeSetId(4);
		$product->setTypeId('virtual');
		$product->setName('Installment');
		$product->setWebsiteIDs(array(Mage::app()->getStore()->getWebsiteId())); 
		$product->setDescription("Please don't delete or update this product.");
		$product->setShortDescription("Please don't delete or update this product.");
		$product->setUrlKey("please_dont_access_this_product");
		$product->setPrice(0.00); 
//		if some required custom attributes then enter any one of it here
		//$product->setHeight('');
		//$product->setWidth('');
		//$product->setDepth('');
		//$product->setType('');
		//Default Magento attribute
		$product->setWeight(4.0000);
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
		}
		catch (Exception $ex) {
		    //Handle the error
		}
	    }
	}
	catch (Exception $e) {
	    throw $e;
	}
    }
}

 