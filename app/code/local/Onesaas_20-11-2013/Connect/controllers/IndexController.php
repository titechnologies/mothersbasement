<?php
/*
  IndexController.php

  OneSaas Connect API 1.0.6.10 for Magento 1.5.1.0
  http://www.onesaas.com

  Copyright (c) 2012 oneSaas

  Version change:
  1.0.6.5 	- Fixed xml encoding for product sku
  			- Added xml formatting if DOM is installed

  1.0.6.6 	- Added htmlspecialchars for all fields left
  			- Removed xml formatting with DOM as it returns an empty doc if the xml is broken (impossible to debug)

  1.0.6.7	- Updated support for pushing Shipping Tracking and Product Stock (also in batch mode)
  			- Added Tax on Shipping Amount for order requests
			- Added parsing LastUpdatedTime as UCT
			- Changed Discount Item to return discount per item instead of line

  1.0.6.8	- Updated support for Product Stock to handle both new batch mode and previous single mode

  1.0.6.9	- Updated Contact Information in OrderResponse.xml to use the first not empty name (first, last) for customer name out of :order->getFirstName, order->getBillingAddress, order->getShippingAddress
  				Necessary as some Magento extensions (M2Pro EBay) do not add name (first, last) in the order table but only in the order addresses

  1.0.6.10	- Updated Order Information in OrderResponse.xml.  Added <Order><Address> for shipping and billing addresses.  If these are not present, OneSaas will use the default adresses for the contact
  
  1.0.6.11	- Changed all currency amounts to be reported in the store base currency (fields in db with prefix "base_" instead of customer selected currency amounts.
  
  1.0.6.12	- Changed order addresses to prevent "Call to a member function on a non-object".
  
  1.0.6.13	- Changed order address street to prevent Notice: Undefined offset in some new php installations
  
  1.0.6.14	- Added Store Credit Discount to Order Discount
  
  1.0.6.15	- Added support for CostPrice (Buying Price)
			- Fixed error in using LastUpdatedTime & OrderCreatedTime
  
  1.0.6.16	- Changed Shipping method
  
  1.0.6.17 - Changed Tax Information reported by Orders and Settings Actions to be compatible with "Tax code mapping upgrade for shopping carts" (https://trello.com/c/w4KjuzAY/1480-tax-code-mapping-upgrade-for-shopping-carts-as-per-description-and-photo-of-whiteboard-session)
*/

class Onesaas_Connect_IndexController extends Mage_Core_Controller_Front_Action{

	private $pageNo;
	private $pageSize = 100;
	private $lstUpdTm;
	private $ordCreatedTm;
	private $xml = '';

	public function indexAction(){
		
		// Initialize XML Response
		header("Content-type:application/xml");
		$this->xml = '<?xml version="1.0" encoding="utf-8"?>';
		$this->xml .= '<OneSaas version="' . $this->getOneSaasConnectVersion() . '">';

		// Verify AccessKey
		if($this->verifyAccessKey($_GET['AccessKey'])) {

			// Parse parameters and initiliase variables
			$OrderCreatedTime = (isset($_GET['OrderCreatedTime']) && (strtotime($_GET['OrderCreatedTime'])>0)) ? Date('Y-m-d H:i:sP', strtotime($_GET['OrderCreatedTime'].'UCT')) : '1970-01-19T00:00:00+00:00';
			$LastUpdatedTime = (isset($_GET['LastUpdatedTime']) && (strtotime($_GET['LastUpdatedTime'])>0)) ? Date('Y-m-d H:i:sP', strtotime($_GET['LastUpdatedTime'].'UCT')) : '1970-01-19T00:00:00+00:00';
			$LUT = explode('T',max(trim($LastUpdatedTime), trim($OrderCreatedTime)));
			$OCT = explode('T',trim($OrderCreatedTime));
			$this->lstUpdTm = implode(" ",$LUT);
			$this->ordCreatedTm = implode(" ",$OCT);
			$this->pageNo = (((isset($_GET['Page']) && (is_numeric($_GET['Page']))) ? (int) $_GET['Page'] : 0)) + 1;	// OS uses 0 for first page
			/*
			$this->xml .= '<LastUpdatedTimeP>' . $_GET['LastUpdatedTime'] . '</LastUpdatedTimeP>';
			$this->xml .= '<OrderCreatedTimeP>' . $_GET['OrderCreatedTime'] . '</OrderCreatedTimeP>';
			$this->xml .= '<lstUpdTime>' . strtotime($_GET['LastUpdatedTime']) . '</lstUpdTime>';
			$this->xml .= '<ordCreatedTime>' . strtotime($_GET['OrderCreatedTime']) . '</ordCreatedTime>';
			$this->xml .= '<LastUpdatedTime>' . $LastUpdatedTime . '</LastUpdatedTime>';
			$this->xml .= '<OrderCreatedTime>' . $OrderCreatedTime . '</OrderCreatedTime>';
			$this->xml .= '<lstUpdTm>' . $this->lstUpdTm . '</lstUpdTm>';
			$this->xml .= '<ordCreatedTm>' . $this->ordCreatedTm . '</ordCreatedTm>';
			$this->xml .= '<toBeUsed>' . max($LastUpdatedTime, $OrderCreatedTime) . '</toBeUsed>';
			*/
			//Check action
			$action = (isset($_GET['Action']) ? $_GET['Action'] : '');
			switch ($action) {
				case 'Contacts':
					$this->contactsAction();
					break;
				case 'Products':
					$this->productsAction();
					break;
				case 'Orders':
					$this->ordersAction();
					break;
				case 'Settings':
					$this->configurationsAction();
					break;
				case 'UpdateStock':
					$this->updateStockAction();
					break;
				case 'ShippingTracking':
					$this->shippingtrackingAction();
					break;
				default:
					$this->xml .= "<Error>Invalid Action</Error>";
			}
		} else {
			$this->xml .= "<Error>Invalid API Key</Error>";
		}

		$this->xml .= '</OneSaas>';

		/* To be uncommented in stable release
		// If DOM is installed, display the result as formatted XML
		if (class_exists('DOMDocument')) {
    		$dom = new DOMDocument();
			$dom->loadXML($this->xml);
			$dom->formatOutput = true;
			$this->xml = $dom->saveXML();
		}
		*/
		echo $this->xml;
	}


	/*** Product API Functions Start ***/

	public function productsAction(){

		if(!$this->getPageNoIsValid('Product')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getProducts();
		}
	}

	private function getProductModel(){
		return Mage::getModel('catalog/product');
	}

	private function getProductCollection($pageSize,$pageNo,$lstUpdTm){
		$prodModel = $this->getProductModel();
		$prod_data = $prodModel->getCollection()
								->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
								->setPageSize($pageSize)
								->setCurPage($pageNo)
								->getData();
		return $prod_data;
	}

	private function getProducts() {
		$content = '';
		$prodCol = $this->getProductCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		foreach($prodCol as $prod){
			$content .= $this->getProductInfo($prod['entity_id'],$prod['updated_at']);
		}
		return $content;
	}

	private function getProductInfo($id,$LUD){
		$prod = $this->getProductModel()->load($id);
		$type = $prod->getTypeId();
		$actionKey = Mage::getSingleton('adminhtml/url')->getSecretKey("catalog_product", "edit");
		$url = Mage::getBaseUrl()."admin/catalog_product/edit/id/".$id."/key/".$actionKey;

		if($prod->getStatus() == 1)
			$prodStatus = "True";
		else
			$prodStatus = "False";
		if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($prod)->getManageStock() == 1)
			$isInventoried = "True";
		else
			$isInventoried = "False";
		$prod_info = array(
							'Code' => htmlspecialchars($prod->getSku()),
							'Name' => htmlspecialchars($prod->getName()),
							//'Model' => htmlspecialchars($prod->getModel()),
							'Description'=>htmlspecialchars(strip_tags($prod->getShortDescription())),
							'IsActive'=>$prodStatus,
							'Url'=>htmlspecialchars($url),
							'PublicUrl'=>htmlspecialchars($prod->getProductUrl()),
							'SalePrice'=>htmlspecialchars($prod->getPrice()),
							'CostPrice'=>htmlspecialchars($prod->getCost()),
							'StockAtHand'=>htmlspecialchars(Mage::getModel('cataloginventory/stock_item')->loadByProduct($prod)->getQty()),
							'IsInventoried'=>$isInventoried,
							'Type'=>($type=='simple')?'Product':$type
						);
		if($type == 'grouped' or $type == 'bundle') {
			$aProdsIdsArr = $prod->getTypeInstance()->getChildrenIds($id);
			$comboItems = array();
			foreach($aProdsIdsArr as $aProdsIds){
				foreach($aProdsIds as $aProdId){
					$aProd = $this->getProductModel()->load($aProdId);
					$comboItems[] = array('ProductId'=>$aProdId,'Quantity'=>Mage::getModel('cataloginventory/stock_item')->loadByProduct($aProd)->getQty());
				}
			}
			$comboItemsArr = array('ComboItems'=>$comboItems);
			$prod_info = array_merge($prod_info,$comboItemsArr);
		}
		return $this->getInfoXml('Product',$id,$LUD,$prod_info);
	}

	/*** Product API Functions End ***/

	/*** Customer API Functions Start ***/

	public function contactsAction(){
		if(!$this->getPageNoIsValid('Contact')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getCustomers();
		}
	}

	private function getCustomerModel(){
		return Mage::getModel('customer/customer');
	}

	private function getCustomerCollection($pageSize,$pageNo,$lstUpdTm){
		$custModel = $this->getCustomerModel();
		$cust_data = $custModel->getCollection()
								->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
								->setPageSize($pageSize)
								->setCurPage($pageNo)
								->getData();
		return $cust_data;
	}

	private function getCustomers(){
		$content = '';
		$custCol = $this->getCustomerCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		foreach($custCol as $cust) {
			$content .= $this->getCustomerInfo($cust['entity_id'],$cust['updated_at']);
		}
		return $content;
	}

	private function getCustomerInfo($id,$LUD){
		$cust = $this->getCustomerModel()->load($id);
		$group = Mage::getModel('customer/group')->load($cust->getGroupId());
		$custBillingAdd = Mage::getModel('customer/address')->load($cust->getDefaultBilling());
		$custShippingAdd = Mage::getModel('customer/address')->load($cust->getDefaultShipping());
		$streetBill = $custBillingAdd->getStreet();
		$streetShip = $custShippingAdd->getStreet();
		// Please note this Url requires login
		$url = Mage::getBaseUrl()."admin/customer/edit/id/".$id;

		if(!$salutation = $cust->getPrefix()){$salutation = '';}
		if(!$taxvat = $cust->getTaxvat()){$taxvat = '';}
		$cust_info = array(
							'Salutation' => htmlspecialchars($salutation),
							'FirstName' => htmlspecialchars($cust->getFirstname()),
							'LastName' => htmlspecialchars($cust->getLastname()),
							//'Group' => $group->getCode(),
							'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
							'Email'=>htmlspecialchars($cust->getEmail()),
							'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
							'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
							'Addresses'=>array(
												'Address type="Billing"'=>array(
																'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
																'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
																'City'=>htmlspecialchars($custBillingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
																'State'=>htmlspecialchars($custBillingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
																),
												'Address type="Shipping"'=>array(
																'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
																'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
																'City'=>htmlspecialchars($custShippingAdd->getCity()),
																'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
																'State'=>htmlspecialchars($custShippingAdd->getRegion()),
																'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
																)
											),
							'Url'=>htmlspecialchars($url)
						);
		return $this->getInfoXml('Contact',$id,$LUD,$cust_info);
	}

	/*** Customer API Functions End ***/

	/*** Order API Functions Start ***/

	public function ordersAction(){
		if(!$this->getPageNoIsValid('Order')) {
			$this->xml .= "";
		} else {
			$this->xml .= $this->getOrders();
		}
	}

	private function getOrderModel(){
		return Mage::getModel('sales/order');
	}

	private function getOrderCollection($pageSize,$pageNo,$lstUpdTm){
		$ordModel = $this->getOrderModel();
		$ord_data = $ordModel->getCollection()
								->addFieldToFilter('updated_at', array('gt' => $lstUpdTm))
								->setPageSize($pageSize)
								->setCurPage($pageNo)
								->getData();
		return $ord_data;
	}

	private function getOrders(){
		$content = '';
		$ordCol = $this->getOrderCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		foreach($ordCol as $ord){
			$content .= $this->getOrderInfo($ord['entity_id'],$ord['updated_at']);
		}
		return $content;
	}
	
	// Try to match the tax rate ($rate) with the taxes applied to the order ($fullTaxInfo) to retrieve the name of the tax
	private function getTaxNameForItem($fullTaxInfo, $rate) {
		if (!isset($fullTaxInfo) || !is_array($fullTaxInfo) || count($fullTaxInfo)==0 || !is_numeric($rate)) {
			return "";
		}
		foreach($fullTaxInfo as $taxInfo) {
			if (is_array($taxInfo) && isset($taxInfo['rates']) && is_array($taxInfo['rates']) && count($taxInfo['rates']>0)) {
				foreach($taxInfo['rates'] as $taxRate) {
					if (is_array($taxRate) && isset($taxRate['code']) && isset($taxRate['percent']) && is_numeric($taxRate['percent']) && (abs((0.0 + $taxRate['percent']) - (0.0 + $rate)) < 0.01)) {
						return $taxRate['code'];
					}
				}
			}
		}
		return "";
	}
	
	private function getOrderInfo($id,$LUD){
		$order = $this->getOrderModel()->load($id);
		$items = $order->getAllVisibleItems();
		$items_arr = array();
		foreach($items as $index => $item){
			$item_arr_inner = array('ProductId'=>$item->getProductId(),
									'ProductCode'=>htmlspecialchars($item->getSku()),
									'ProductName'=>htmlspecialchars($item->getName()),
									'Quantity'=>htmlspecialchars($item->getQtyOrdered()),
									'Price'=>htmlspecialchars($item->getBasePriceInclTax()),
									'UnitPriceExTax'=>htmlspecialchars($item->getBasePrice()));
			if ($item->getBaseDiscountAmount()>0) {$item_arr_inner['Discount'] = htmlspecialchars($item->getBaseDiscountAmount()/$item->getQtyOrdered());}
			if ($item->getBaseTaxAmount()>0) {$item_arr_inner['Taxes'] = array(
																			'Tax'=>array(
																				'TaxName'=>$this->getTaxNameForItem($order->getFullTaxInfo(), $item->getTaxPercent()),
																				'TaxRate'=>htmlspecialchars((0.0 + $item->getTaxPercent())/100),
																				'TaxAmount'=>htmlspecialchars($item->getBaseTaxAmount())));}

			$item_arr_inner['LineTotalIncTax']=$item->getBaseRowTotalInclTax();
			$items_arr['Item_' . $index]=$item_arr_inner;
		}
		$actionKey = Mage::getSingleton('adminhtml/url')->getSecretKey("sales_order","view");
		$url = Mage::getBaseUrl()."admin/sales_order/view/order_id/".$id."/key/".$actionKey;
		$payments = $order->getAllPayments();
		$payments_array = array();
		foreach($payments as $key=>$a_payment) {
			try {
				$paymentMethodName = $a_payment->getMethodInstance()->getCode();
			} catch (Exception $e) {
				// Do nothing for now...
			}
			$payments_array['PaymentMethod'] = array(
				'MethodName' => htmlspecialchars($paymentMethodName),
				'Amount'=>htmlspecialchars($a_payment->getBaseAmountPaid()));
		}

		$shippings_array = array();
		$carrier_code = '';
		$shipping_carrier = $order->getShippingCarrier();
		if (!is_null($shipping_carrier) && is_object($shipping_carrier)) {
			$carrier_code = $shipping_carrier->getCarrierCode();
		}
		//Mage::getModel('sales/order')->load($id)->getShippingDescription()
		
		$shippings_array = array(
			'ShippingMethod'=>htmlspecialchars($order->getShippingDescription()),
			//'ShippingMethod'=>htmlspecialchars($carrier_code),
			'Amount'=>htmlspecialchars($order->getBaseShippingAmount()+$order->getData('base_shipping_tax_amount')));

		$credits = $order->getCreditmemosCollection();
		$credits_array = array();

		foreach($credits as $key => $a_credit) {
			$comments_array = array();
			foreach ( $a_credit->getCommentsCollection() as $j => $a_comment) {
				$comments_array['Comment_' . $j . ' LastUpdated="' . htmlspecialchars($a_comment->getCreatedAt()) . '"'] = htmlspecialchars($a_comment->getComment());
			}
			$credits_array['Credit LastUpdated="' . htmlspecialchars($a_credit->getUpdatedAt()) . '"'] = array('Date'=>htmlspecialchars($a_credit->getCreatedAt()), 'Amount'=>htmlspecialchars($a_credit->getBaseGrandTotal()), 'Comments'=> $comments_array);
		}
		// Contact information
		if (is_null($order->getCustomerId())) {
			$contact_tag = 'Contact';
			$custBillingAdd = $order->getBillingAddress();
			$custShippingAdd = $order->getShippingAddress();
			if (!$custShippingAdd && $custBillingAdd) {
				$custShippingAdd = $custBillingAdd;
			}
			if ($custShippingAdd && !$custBillingAdd) {
				$custBillingAdd = $custShippingAdd;
			}
			
			if ($custShippingAdd && $custBillingAdd) {
				$streetBill = $custBillingAdd->getStreet();
				$streetShip = $custShippingAdd->getStreet();
				if(!$salutation = $order->getData('customer_prefix')){$salutation = '';}
				if(!$taxvat = $order->getData('customer_taxvat')){$taxvat = '';}
				$customerFirstname = ($order->getCustomerFirstname() == '')?(($custBillingAdd->getFirstname()=='')?$custShippingAdd->getFirstname():$custBillingAdd->getFirstname()):$order->getCustomerFirstname();
				$customerLastname = ($order->getCustomerLastname() == '')?(($custBillingAdd->getLastname()=='')?$custShippingAdd->getLastname():$custBillingAdd->getLastname()):$order->getCustomerLastname();

				$contact_info = array(
								'Salutation' => htmlspecialchars($salutation),
								'FirstName' => htmlspecialchars($customerFirstname),
								'LastName' => htmlspecialchars($customerLastname),
								'BillingFirstName' => htmlspecialchars($custBillingAdd->getFirstname()),
								'BillingLastName' => htmlspecialchars($custBillingAdd->getLastname()),
								'ShippingFirstName' => htmlspecialchars($custShippingAdd->getFirstname()),
								'ShippingLastName' => htmlspecialchars($custShippingAdd->getLastname()),
								'WorkPhone'=>htmlspecialchars($custBillingAdd->getTelephone()),
								'Email'=>htmlspecialchars($order->getData('customer_email')),
								'OrganizationName'=>htmlspecialchars($custBillingAdd->getCompany()),
								'OrganizationBusinessNumber'=>htmlspecialchars($taxvat),
								'Addresses'=>array(
													'Address type="Billing"'=>array(
																	'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
																	'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
																	'City'=>htmlspecialchars($custBillingAdd->getCity()),
																	'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
																	'State'=>htmlspecialchars($custBillingAdd->getRegion()),
																	'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
																	),
													'Address type="Shipping"'=>array(
																	'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
																	'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
																	'City'=>htmlspecialchars($custShippingAdd->getCity()),
																	'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
																	'State'=>htmlspecialchars($custShippingAdd->getRegion()),
																	'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
																	)
												)
							);
			} else {
				$contact_info = null;
			}
		} else {
			$contact_tag = 'Contact id="'. $order->getCustomerId() .'"';
			$contact_info = '';
		}

		// Billing & Shipping Optional Addresses
		$custBillingAdd = $order->getBillingAddress();
		$custShippingAdd = $order->getShippingAddress();
		if (!$custShippingAdd && $custBillingAdd) {
			$custShippingAdd = $custBillingAdd;
		}
		if ($custShippingAdd && !$custBillingAdd) {
			$custBillingAdd = $custShippingAdd;
		}
		
		if ($custShippingAdd && $custBillingAdd) {
			$streetBill = $custBillingAdd->getStreet();
			$streetShip = $custShippingAdd->getStreet();
				
			$addresses = array(
							'Address type="Billing"'=>array(
											'Salutation'=>htmlspecialchars(($salutation = $custBillingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>htmlspecialchars($custBillingAdd->getFirstname()),
											'LastName'=>htmlspecialchars($custBillingAdd->getLastname()),
											'OrganizationName'=>htmlspecialchars(($company = $custBillingAdd->getData('company'))?$company:''),
											'Line1'=>htmlspecialchars(isset($streetBill[0])?$streetBill[0]:""),
											'Line2'=>htmlspecialchars(isset($streetBill[1])?$streetBill[1]:""),
											'City'=>htmlspecialchars($custBillingAdd->getCity()),
											'PostCode'=>htmlspecialchars($custBillingAdd->getPostcode()),
											'State'=>htmlspecialchars($custBillingAdd->getRegion()),
											'CountryCode'=>htmlspecialchars($custBillingAdd->getCountryId())
											),
							'Address type="Shipping"'=>array(
											'Salutation'=>htmlspecialchars(($salutation = $custShippingAdd->getData('prefix'))?$salutation:''),
											'FirstName'=>htmlspecialchars($custShippingAdd->getFirstname()),
											'LastName'=>htmlspecialchars($custShippingAdd->getLastname()),
											'OrganizationName'=>htmlspecialchars(($company = $custShippingAdd->getData('company'))?$company:''),
											'Line1'=>htmlspecialchars(isset($streetShip[0])?$streetShip[0]:""),
											'Line2'=>htmlspecialchars(isset($streetShip[1])?$streetShip[1]:""),
											'City'=>htmlspecialchars($custShippingAdd->getCity()),
											'PostCode'=>htmlspecialchars($custShippingAdd->getPostcode()),
											'State'=>htmlspecialchars($custShippingAdd->getRegion()),
											'CountryCode'=>htmlspecialchars($custShippingAdd->getCountryId())
											)
						);
		}
		if (is_null($contact_info)) {
			return "";
		}
		
		$ord_info = array(
							'OrderNumber' => htmlspecialchars($order->getIncrementId()),
							'Date' => htmlspecialchars($order->getCreatedAt()),
							'Type'=>"Order",
							'Status'=>htmlspecialchars($order->getStatus()),
							'Notes'=>htmlspecialchars($order->getCustomerNote()),
							'Tags'=>'StoreName:' . htmlspecialchars($order->getStoreName()) .',',
							'Discounts'=>htmlspecialchars(0.0 + abs($order->getBaseDiscountAmount()) + abs($order->getBaseCreditDiscountAmount())),
							'Total'=>htmlspecialchars($order->getBaseGrandTotal()),
							$contact_tag => $contact_info,
							'Addresses' => $addresses,
							'Items'=>$items_arr,
							'Shipping'=>$shippings_array,
							'Payments'=>$payments_array,
							'Credits'=>$credits_array,
							'Url'=>htmlspecialchars($url)
						);
		return $this->getXml('Order',array('id'=>$id,'LastUpdated'=>$LUD),$ord_info);
	}

	/*** Order API Functions End ***/

	/*** Configuration API Functions Start ***/

	public function configurationsAction(){
		$this->xml .= $this->getConfiguration();
	}

	private function getPaymentConfigModel(){
		return Mage::getSingleton('payment/config');
	}

	private function getTaxRateModel(){
		return Mage::getModel('tax/calculation_rate');
	}

	private function getShippingModel(){
		return Mage::getModel('shipping/config');
	}

	private function getConfigCollection($pageSize,$pageNo,$lstUpdTm){
		$paymentModel = $this->getPaymentConfigModel();
		$activeMethods = $paymentModel->getActiveMethods();
		$paymentMethods = array();
		foreach ($activeMethods as $paymentCode=>$paymentModel){
			$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			$paymentMethods[]=array('Name'=>$paymentCode, 'Description'=>htmlspecialchars(strip_tags($paymentTitle)));
		}

		$trModel = $this->getTaxRateModel();
		$tr_data = $trModel->getCollection()->getData();
		$trs = array();
		foreach($tr_data as $tr){
			$stateCode = Mage::getModel('directory/region')->load($tr['tax_region_id'])->getCode();
			$trs[]=array('Name'=>htmlspecialchars($tr['code']),'Description'=>'', 'Rate'=>(0.0 + $tr['rate'])/100, 'CountryCode'=>htmlspecialchars($tr['tax_country_id']), 'StateCode'=>htmlspecialchars($stateCode), 'Zip'=>htmlspecialchars($tr['tax_postcode']),'City'=>'','IsCompound'=>'');
		}

		$shipModel = $this->getShippingModel();
		$aCarriers = $shipModel->getActiveCarriers();
		$ShippingMethods=array();
		foreach ($aCarriers as $carrierCode=>$carrierModel){
			$carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
			$ShippingMethods[]=array('Name'=>htmlspecialchars($carrierCode), 'Description'=>htmlspecialchars(strip_tags($carrierTitle)));
		}

		$config=array('PaymentGateways'=>$paymentMethods,'TaxCodes'=>$trs,'ShippingMethods'=>$ShippingMethods);

		return $config;
	}

	private function getConfiguration(){
		$content = '';
		$confCol = $this->getConfigCollection($this->pageSize,$this->pageNo,$this->lstUpdTm);
		$count = 0;
		$found = 0;
		$pageNo = (($this->pageNo-1)*$this->pageSize)+1;
		$endpageNo = $this->pageSize*$this->pageNo;
		foreach($confCol as $key=>$conf){
			$count++;

			if(($pageNo<=$count) AND ($count<=$endpageNo)) {
				$content .= $this->getConfigInfo($key,$conf);
				$found = 1;
			}
		}
		if($found == 0) {
			$content .= '';
		}
		return $content;
	}

	private function getConfigInfo($key,$conf){

		return $this->getInfoXml($key,false,null,$conf);

	}

	/*** Configuration API Functions End ***/

	/*** Order Shipping Tracking API Functions Start ***/

	public function shippingtrackingAction(){
		// Parse posted parameters ShippingTrackingId, OrderNumber, Date, TrackingCode, CarrierCode, CarrierName, Notes
		$xmlRequest = new SimpleXmlElement(file_get_contents("php://input"));
		if ($xmlRequest->getName()==='OrderShippingTracking') {
			foreach ($xmlRequest->attributes() as $attr) {
				if ($attr->getName() === 'Id') {
					$OrderNumber = "" . $attr;
				}
			}
			foreach ($xmlRequest->children() as $child) {
				switch ($child->getName()) {
					case 'OrderNumber':
						$OrderIncrementId = $child;
						break;
					case 'Date':
						$Date = $child;
						break;
					case 'TrackingCode':
						$TrackingCode = $child;
						break;
					case 'CarrierCode':
						$CarrierCode = $child;
						break;
					case 'CarrierName':
						$CarrierName = $child;
						break;
					case 'Notes':
						$Notes = $child;
						break;
					default:
						// Not interested
						break;
				}
			}
			if ( ($OrderNumber != '') ) {
				try {
					$order = Mage::getModel('sales/order')->load($OrderNumber);
					if($order->canShip()) {
						$itemQty =  $order->getItemsCollection()->count();
						$ship = new Mage_Sales_Model_Order_Shipment_Api();
						$shipmentId = $ship->create($order->getIncrementId());
					}

					$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
					$shipment_collection->addAttributeToFilter('order_id', $order->getId());

					foreach($shipment_collection as $sc) {
						$shipment = Mage::getModel('sales/order_shipment');
						$shipment->load($sc->getId());
						if($shipment->getId() != '') {
							$track = Mage::getModel('sales/order_shipment_track')
									 ->setShipment($shipment)
									 ->setData('title', $CarrierName)
									 ->setData('number', $TrackingCode)
									 ->setData('carrier_code', $CarrierCode)
									 ->setData('description', $Notes)
									 ->setData('order_id', $shipment->getData('order_id'))
									 ->save();
							}
						if($shipment){
							if(!$shipment->getEmailSent()){
								$shipment->sendEmail(true);
								$shipment->setEmailSent(true);
								$shipment->save();
							}
						}
					}
					$this->xml .= '<Success>Operation Succeeded</Success>';
				} catch (Exception $ex) {
					$this->xml .= '<Error>Shipping tracking update failed: ' . $ex . '. OrderId=' . $OrderNumber . ' OrderIncrementalId=' . $OrderIncrementId . '</Error>';
				}
			} else {
				$this->xml .= '<Error>Wrong parameters: Order Number = ' . $OrderNumber . '</Error>';
			}
		} else {
			$this->xml .= '<Error>Wrong xml format</Error>';
		}
	}

	/*** Order Shipping Tracking API Functions End ***/

	/*** Update Stock API Functions Start ***/

	public function updateStockAction() {
		// Parse posted parameters StockUpdateId, ProductCode, StockAtHand, StockAllocated, StockAvailable
		$xmlRequest = new SimpleXmlElement(file_get_contents("php://input"));
		$stockUpdateRequests = array();
		$batchMode='false';
		if ($xmlRequest->getName()==='ProductStockUpdate') {
			// Single product stock update
			$stockUpdateRequests[] = $this->parseSingleStockUpdateRequest($xmlRequest);
		} elseif ($xmlRequest->getName()==='ProductStockUpdates') {
			// Multiple product stock update
			$batchMode='true';
			foreach ($xmlRequest->children() as $aXmlRequest) {
				$stockUpdateRequests[] = $this->parseSingleStockUpdateRequest($aXmlRequest);
			}
		} else {
			// Wrong format
		}

		// Xml Response depends on batchMode
		$this->xml .= ($batchMode=='true')?'<ProductStockUpdates>':'';
		foreach ($stockUpdateRequests as $stockUpdateRequest) {
			$this->xml .= ($batchMode=='true')?'<ProductStockUpdate Id=' . $stockUpdateRequest['ProductCode'] .'>':'';
			if ($stockUpdateRequest['ProductCode'] != '') {
				$product = Mage::getModel('catalog/product')->load($stockUpdateRequest['ProductCode']);
				if (is_object($product)) {
					$stock_obj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
					$stockData = $stock_obj->getData();
					$stockData['qty'] = $stockUpdateRequest['StockAvailable'];
					$stockData['is_in_stock'] = 1;
					$stock_obj->setData($stockData);
					try {
						$stock_obj->save();
						$product->save();
						$this->xml .= '<Success>Operation Succeeded. Batch mode=' . $batchMode . '</Success>';
					} catch (Exception $ex) {
						$this->xml .= '<Error>Stock update failed: ' . $ex . '</Error>';
					}
				} else {
					$this->xml .= '<Error>Wrong parameters: Product is not object. Id=' . $stockUpdateRequest['ProductCode'] . ' StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
				}
			} else {
				$this->xml .= '<Error>Wrong parameters: Id=' . $stockUpdateRequest['ProductCode'] . ' StockAvailable=' . $stockUpdateRequest['StockAvailable'] . '</Error>';
			}
		}
		$this->xml .= ($batchMode=='true')?'</ProductStockUpdates>':'';
	}

	/*** Update Stock API Functions End ***/

	/*** General Functions Start ***/
	private function parseSingleStockUpdateRequest(SimpleXmlElement $aRequest) {
		$stockUpdateRequest = array();
		if (!is_null($aRequest) && $aRequest->getName()==='ProductStockUpdate') {
			foreach ($aRequest->attributes() as $attr) {
				if ($attr->getName() === 'Id') {
					$stockUpdateRequest['ProductCode'] = $attr;
				}
			}
			foreach ($aRequest->children() as $child) {
				switch ($child->getName()) {
					case 'StockAtHand':
						$stockUpdateRequest['StockAtHand'] = $child;
						break;
					case 'StockAllocated':
						$stockUpdateRequest['StockAllocated'] = $child;
						break;
					case 'StockAvailable':
						$stockUpdateRequest['StockAvailable'] = (int) $child;
						break;
					default:
						// Not interested
						break;
				}
			}
			$stockUpdateRequest;
		}
		return $stockUpdateRequest;
	}

	private function getInfoXml($entity,$id,$LUD,$info){
		if(!$id){
			$xmlStr = "<$entity>";
		}elseif($id and $LUD == null){
			$xmlStr = "<$entity id=\"$id\">";
		}else{
			$xmlStr = "<$entity id=\"$id\" LastUpdated=\"$LUD\">";
		}
		if(is_array($info)){
			foreach($info as $key=>$val){
				if(!(is_array($val))){
					$xmlStr .= "<$key>$val";
					$key_arr = explode(' ',$key);
					$xmlStr .= "</$key_arr[0]>";
				}else{
					if($entity == 'PaymentGateways'){
						$key = 'PaymentGateway';
					}elseif($entity == 'TaxCodes'){
						$key = 'TaxCode';
					}elseif($entity == 'ShippingMethods'){
						$key = 'ShippingMethod';
					}
					$xmlStr .= "<$key>";
					foreach($val as $k=>$v){
						if(!(is_array($v))){
							$xmlStr .= "<$k>$v</$k>";
						}else{
							if($entity == 'Order' and $key == 'Items' and is_int($k)){
								$k = 'Item';
							}elseif($entity == 'Product' and $key == 'ComboItems' and is_int($k)){
								$k = 'ComboItem';
							}
							$xmlStr .= "<$k>";
							foreach($v as $k2=>$v2){
								if($k2 != 'tagAttribs'){
									$xmlStr .= "<$k2>$v2</$k2>";
								}
							}
							$k_arr = explode(' ',$k);
							$xmlStr .= "</$k_arr[0]>";
						}
					}
					$key_arr = explode(' ',$key);
					$xmlStr .= "</$key_arr[0]>";
				}
			}
		}
		$xmlStr .= "</$entity>";
		return $xmlStr;
	}

	private function getXml($entity,$attributes=array(),$info) {
		$xmlStr = '';
		if (!is_null($attributes) && is_Array($attributes) && count($attributes)>0) {
			$xmlStr .= '<' . $entity;
			foreach ($attributes as $name => $value) {
				$xmlStr .= ' ' . $name . '="' . $value .'"';
			}
			$xmlStr .= '>';
		} else {
			$xmlStr .= "<$entity>";
		}
		if (is_array($info)) {
			foreach($info as $key=>$val) {
				if (strrpos($key,'_')) {
					$begin = substr($key,0,strrpos($key,'_'));
					$end = substr($key,strrpos($key,'_'));
					($first_sapce = strpos($end,' '))?$end = substr($end,$first_sapce):$end = '';
					$key = $begin . $end;
				}
				$xmlStr .= $this->getXml($key,null,$val);
			}
		} else {
			$xmlStr .= $info;
		}
		$closingTag = explode(' ', $entity);
		$xmlStr .= '</' . $closingTag[0] . '>';

		return $xmlStr;
	}

	private function getAccessKey(){
		$helper =  $this->getHelper();
		$key = $helper->getKey();
		return $key;
	}

	private function verifyAccessKey($userKey){
		$key = $this->getAccessKey();
		if($userKey === $key){
			return true;
		}
	}

	private function getHelper(){
		return Mage::helper('osconnect');
	}

	private function getOneSaasConnectVersion(){
		//$mod_info = (array)Mage::getConfig()->getNode('modules/Onesaas_Connect')->children();
		return '1.0.6.17';
	}

	private function getPageNoIsValid($entity){

		if($entity == 'Product') {

			$productCollections = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getData();

			$proCount = ceil(count($productCollections)/$this->pageSize);

			if($this->pageNo>$proCount) {
				return False;
			} else {
      			return True;
      		}
		}
		if($entity == 'Contact') {

			$customerCollections = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getData();

			$custCount = ceil(count($customerCollections)/$this->pageSize);

			if($this->pageNo>$custCount)

				return False;
			else

      			return True;

		}
		if($entity == 'Order') {

			$orderCollections = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('updated_at', array('gt' => $this->lstUpdTm))->getData();

			$orderCount = ceil(count($orderCollections)/$this->pageSize);

			if($this->pageNo>$orderCount)

				return False;
			else

      			return True;

		}
	}

		/*** General Functions End ***/
}
?>
