<?php
/**
 * VES_PdfPro_Model_Observer
 *
 * @author		VnEcoms Team <support@vnecoms.com>
 * @website		http://www.vnecoms.com
 */
class VES_PdfPro_Model_Observer
{
	/**
	 * Add new link to Sales Order Massaction
	 * @param Varien_Event_Observer $observer
	 */
	public function core_block_abstract_prepare_layout_before(Varien_Event_Observer $observer){
		if(!Mage::getStoreConfig('pdfpro/config/enabled') || !Mage::helper('pdfpro')->getDefaultApiKey()) return;
		$block = $observer->getEvent()->getBlock();
		if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && in_array($block->getRequest()->getControllerName(),array('sales_order','adminhtml_sales_order')))
		{
			if(!Mage::getStoreConfig('pdfpro/config/remove_default_print')){
				if(Mage::getStoreConfig('pdfpro/config/admin_print_order')){
					$block->addItem('easypdf-print-orders', array(
							'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Orders'),
							'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/orders'),
					));
				}
				$block->addItem('easypdf-print-invoices', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Invoices'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/invoices'),
				));
				$block->addItem('easypdf-print-shipments', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Packingslips'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/shipments'),
				));
				$block->addItem('easypdf-print-creditmemos', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Credit Memos'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/creditmemos'),
				));
				$block->addItem('easypdf-print-all', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print All'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/all'),
				));
			}
		}else if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && ($block->getRequest()->getControllerName() == 'sales_invoice' || $block->getRequest()->getControllerName() == 'sales_order_invoice')){
			$block->addItem('easypdf-print-invoices', array(
					'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Invoices'),
					'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/invoices'),
			));
		}else if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && ($block->getRequest()->getControllerName() == 'sales_shipment' || $block->getRequest()->getControllerName() == 'sales_order_shipment')){
			if(!Mage::getStoreConfig('pdfpro/config/remove_default_print')){
				$block->addItem('easypdf-print-shipments', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Packingslips'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/shipments'),
				));
			}
		}else if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && ($block->getRequest()->getControllerName() == 'sales_creditmemo' || $block->getRequest()->getControllerName() == 'sales_order_creditmemo')){
			$block->addItem('easypdf-print-creditmemos', array(
					'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Credit Memos'),
					'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/creditmemos'),
			));
		}
    }
    
    public function core_block_abstract_to_html_before(Varien_Event_Observer $observer){
    	if(!Mage::getStoreConfig('pdfpro/config/enabled') || !Mage::helper('pdfpro')->getDefaultApiKey()) return;
    	$block = $observer->getEvent()->getBlock();
    	if(!Mage::getStoreConfig('pdfpro/config/remove_default_print')) return;
    	if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && in_array($block->getRequest()->getControllerName(),array('sales_order','adminhtml_sales_order'))){
	    		$block->removeItem('pdfinvoices_order');
	    		$block->removeItem('pdfshipments_order');
	    		$block->removeItem('pdfcreditmemos_order');
	    		$block->removeItem('pdfdocs_order');
	    		$block->removeItem('print_shipping_label');
	    		
	    		if(Mage::getStoreConfig('pdfpro/config/admin_print_order')){
				
		    	$block->addItem('easypdf-print-orders', array(
							'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Orders'),
							'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/orders'),
					));
				}
				$block->addItem('easypdf-print-invoices', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Invoices'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/invoices'),
				));
				$block->addItem('easypdf-print-shipments', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Packingslips'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/shipments'),
				));
				$block->addItem('easypdf-print-creditmemos', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Credit Memos'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/creditmemos'),
				));
				$block->addItem('easypdf-print-all', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print All'),
						'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/all'),
				));
				$block->addItem('print_shipping_label', array(
		             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
		             'url'  => Mage::getUrl('adminhtml/sales_order_shipment/massPrintShippingLabel'),
		        ));
    			if(Mage::getStoreConfig('deleteorder/config/enabled')){
	    			$block->removeItem('easypdf-delete-order');
	    			$block->addItem('easypdf-delete-order', array(
						'label'=> 'Easy PDF - '.Mage::helper('deleteorder')->__('Delete Orders'),
						'url'  => Mage::getUrl('deleteorder_cp/adminhtml_index/deleteOrders'),
					));
	    		}
	    		if(Mage::helper('pdfpro')->isEnableModule('EM_DeleteOrder')){
	    			$block->removeItem('delete_order');
	    			$block->addItem('delete_order', array(
						'label'=> Mage::helper('sales')->__('Delete order'),
						'url'  => Mage::getUrl('*/sales_order/deleteorder'),
					));
	    		}
    	}else if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && ($block->getRequest()->getControllerName() == 'sales_invoice' || $block->getRequest()->getControllerName() == 'sales_order_invoice')){
    		$block->removeItem('pdfinvoices_order');
    	}else if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && ($block->getRequest()->getControllerName() == 'sales_shipment' || $block->getRequest()->getControllerName() == 'sales_order_shipment')){
			if(Mage::getStoreConfig('pdfpro/config/remove_default_print')){
				$block->removeItem('pdfshipments_order');
				$block->addItem('easypdf-print-shipments', array(
					'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Packingslips'),
					'url'  => Mage::getUrl('pdfpro_cp/adminhtml_print/shipments'),
				));
			}
		}else if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && ($block->getRequest()->getControllerName() == 'sales_creditmemo' || $block->getRequest()->getControllerName() == 'sales_order_creditmemo')){
			$block->removeItem('pdfcreditmemos_order');
		}
    }
	/**
	 * Write version information from server to local file
	 * @param string $versionFile
	 */
	protected function _writeVersionFile(){
		$versionFile 	= Mage::getBaseDir('media').DS.'ves_pdfpro'.DS.'version.txt';
		$date 			= Mage::getModel('core/date')->date('Y-m-d');
		$serverVersion 	= Mage::helper('pdfpro')->getServerVersion();
		try{
			$fp				= fopen($versionFile, 'w');
			fwrite($fp, base64_encode(json_encode(array('date'=>$date,'version'=>$serverVersion))));
			fclose($fp);
		}catch(Exception $e){
			
		}
		return $serverVersion;
	}
	/**
	 * get Message from server
	 * @param string $versionFile
	 */
	protected function _writeServerMessage(){
		$messageFile 	= Mage::getBaseDir('media').DS.'ves_pdfpro'.DS.'message.txt';
		$message	= Mage::helper('pdfpro')->getServerMessage();
		$date 		= Mage::getModel('core/date')->date('Y-m-d');
		try{
			$fp		= fopen($messageFile, 'w');
			fwrite($fp, base64_encode(json_encode(array('date'=>$date,'message'=>$message))));
			fclose($fp);
		}catch(Exception $e){
			
		}
		return $message;
	}
	/**
	 * Get news and notice from easypdfinvoice.com
	 */
    public function dailyCheckNotification(){
    	try{
    		$this->_writeVersionFile();
    		$this->_writeServerMessage();
    	}catch (Mage_Core_Exception $e){
    		Mage::log($e->getMessage());
    	}catch(Exception $e){
    		Mage::log($e->getMessage());
    	}
    }
    
	public function ves_pdfpro_data_prepare_after($observer){
    	$type = $observer->getType();
    	if($type=='item'){
	    	$itemData = $observer->getSource();
    		$item = $observer->getModel();
    		if ($item instanceof Mage_Sales_Model_Order_Item) {
	            $order = $item->getOrder();
	        } else if ($item instanceof Mage_Sales_Model_Order_Invoice_Item) {
	            $order = $item->getInvoice()->getOrder();
	        } else if ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
	            $order = $item->getShipment()->getOrder();
	        } else if ($item instanceof Mage_Sales_Model_Order_Creditmemo_Item) {
	            $order = $item->getCreditmemo()->getOrder();
	        }
	        
    		$orderCurrencyCode 	= $order->getOrderCurrencyCode();
    		$baseCurrencyCode 	= $order->getBaseCurrencyCode();
    		$itemData->setData('weight',$item->getWeight()*1);
    		$itemData->setData('row_weight',$item->getRowWeight()*1);
    		$itemData->setData('is_virtual',$item->getIsVirtual());
    		$itemData->setData('description',$item->getData('description'));

    		if($itemData->getData('price')){
	    		$itemData->setData('price_incl_tax',Mage::helper('pdfpro')->currency($item->getData('price_incl_tax'),$orderCurrencyCode));
	    		$itemData->setData('price_excl_tax',Mage::helper('pdfpro')->currency($item->getData('price'),$orderCurrencyCode));
	    		$itemData->setData('row_total_incl_tax',Mage::helper('pdfpro')->currency($item->getData('row_total_incl_tax'),$orderCurrencyCode));
	    		$itemData->setData('row_total_excl_tax',Mage::helper('pdfpro')->currency($item->getData('row_total'),$orderCurrencyCode));
	    		$itemData->setData('discount_amount',Mage::helper('pdfpro')->currency($item->getData('discount_amount'),$orderCurrencyCode));
	    		
	    		$itemData->setData('base_cost',Mage::helper('pdfpro')->currency($item->getData('base_cost'),$baseCurrencyCode));
	    		$itemData->setData('base_price',Mage::helper('pdfpro')->currency($item->getData('base_price'),$baseCurrencyCode));
	    		$itemData->setData('base_original_price',Mage::helper('pdfpro')->currency($item->getData('base_original_price'),$baseCurrencyCode));
	    		$itemData->setData('base_tax_amount',Mage::helper('pdfpro')->currency($item->getData('base_tax_amount'),$baseCurrencyCode));
	    		$itemData->setData('base_discount_amount',Mage::helper('pdfpro')->currency($item->getData('base_discount_amount'),$baseCurrencyCode));
	    		$itemData->setData('base_row_total',Mage::helper('pdfpro')->currency($item->getData('base_row_total'),$baseCurrencyCode));
	    		$itemData->setData('base_price_incl_tax',Mage::helper('pdfpro')->currency($item->getData('base_price_incl_tax'),$baseCurrencyCode));
	    		$itemData->setData('base_row_total_incl_tax',Mage::helper('pdfpro')->currency($item->getData('base_row_total_incl_tax'),$baseCurrencyCode));
	    		$itemData->setData('base_discount_amount',Mage::helper('pdfpro')->currency($item->getData('base_discount_amount'),$baseCurrencyCode));
    		}
    		
    		if($item instanceof Mage_Sales_Model_Order_Item){
				$itemData->setData('qty_backordered',$item->getData('qty_backordered')*1);
				$itemData->setData('qty_canceled',$item->getData('qty_canceled')*1);
				$itemData->setData('qty_invoiced',$item->getData('qty_invoiced')*1);
				$itemData->setData('qty_ordered',$item->getData('qty_ordered')*1);
				$itemData->setData('qty_refunded',$item->getData('qty_refunded')*1);
				$itemData->setData('qty_shipped',$item->getData('qty_shipped')*1);
			}
    	}else if($type == 'order'){
    		
    		$orderData 	= $observer->getSource();
    		$order 		= $observer->getModel();
    		
    		$this->addOrderComments($order,$orderData);
    		
    		$this->addAreaToObj($orderData);
    	}else if($type == 'invoice'){
    		$invoiceData 	= $observer->getSource();
    		$invoice 		= $observer->getModel();
    		
    		$this->addComments($type,$invoice,$invoiceData);
    		
    		$this->addAreaToObj($invoiceData);
    	}else if($type == 'shipment'){
    		$shipmentData 	= $observer->getSource();
    		$shipment 		= $observer->getModel();
    		
    		$this->addComments($type,$shipment,$shipmentData);
    		
    		$this->addAreaToObj($shipmentData);
    	}else if($type == 'creditmemo'){
    		$creditmemoData = $observer->getSource();
    		$creditmemo 	= $observer->getModel();
    		
    		$this->addComments($type,$creditmemo,$creditmemoData);
    		
    		$this->addAreaToObj($creditmemoData);
    	}else{
    		//var_dump($observer->getSource()->getCustomer());exit;
    	}
    }
    
    /**
     * Add comment variable for order
     */
    function addOrderComments($order, $orderData){
    	$comments = array();
    	foreach($order->getStatusHistoryCollection(true) as $item){
    		$_item = new Varien_Object($item->getData());
    		$_item->setData('created_date',Mage::helper('core')->formatDate($item->getCreatedAtDate(), 'medium'));
    		$_item->setData('created_time',Mage::helper('core')->formatTime($item->getCreatedAtDate(), 'medium'));
    		$_item->setData('status',$item->getStatusLabel());
    		switch($item->getData('is_customer_notified')){
    			case '0':
    				$_item->setData('customer_notified',Mage::helper('sales')->__('Not Notified'));
    				break;
    			case '1':
    				$_item->setData('customer_notified',Mage::helper('sales')->__('Notified'));
    				break;
    			case '2':
    				$_item->setData('customer_notified',Mage::helper('sales')->__('Notification Not Applicable'));
    				break;
    		}
    		$comments[] = $_item;
    	}
    	$orderData->setData('comments',$comments);
    }
    
    /**
     * Add comment variable for invoice, shipment, creditmemo
     */
	function addComments($type, $model, $source){
    	$comments = array();
    	foreach($model->getCommentsCollection(true) as $comment){
    		$_item = new Varien_Object($comment->getData());
    		$_item->setData('created_date',Mage::helper('core')->formatDate($comment->getCreatedAtDate(), 'medium'));
    		$_item->setData('created_time',Mage::helper('core')->formatTime($comment->getCreatedAtDate(), 'medium'));
    		switch($comment->getData('is_customer_notified')){
    			case '0':
    				$_item->setData('customer_notified',Mage::helper('sales')->__('Not Notified'));
    				break;
    			case '1':
    				$_item->setData('customer_notified',Mage::helper('sales')->__('Notified'));
    				break;
    			case '2':
    				$_item->setData('customer_notified',Mage::helper('sales')->__('Notification Not Applicable'));
    				break;
    		}
    		$comments[] = $_item;
    	}
    	$source->setData('comments',$comments);
    }
    /*
     * Add area variable to objects :order, invoice, shipment, creditmemo
     */
    function addAreaToObj($source){
    	$source->setIsPrintedFromFrontend(Mage::getDesign()->getArea() == 'frontend');
    }
}