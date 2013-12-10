<?php
/**
 * Flint Technology Ltd
 *
 * This module was developed by Flint Technology Ltd (http://www.flinttechnology.co.uk).
 * For support or questions, contact us via http://www.flinttechnology.co.uk/store/contacts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA bundled with this package in the file LICENSE.txt.
 * It is also available online at http://www.flinttechnology.co.uk/store/module-license-1.0
 *
 * @package     flint_feefo-ce-1.2.0.zip
 * @registrant  David Suter
 * @license     68561092-2FBC-43E2-8F1F-450A55AB97CE
 * @eula        Flint Module Single Installation License (http://www.flinttechnology.co.uk/store/module-license-1.0
 * @copyright   Copyright (c) 2012 Flint Technology Ltd (http://www.flinttechnology.co.uk)
 */

?>
<?php
class Flint_FeeFo_OrdersController extends Mage_Core_Controller_Front_Action
{
    private $Dom;

    public function indexAction() {
       $helper = Mage::helper('flint_feefo');

	if($helper->getConfigData('flint_feefo/general/key') && (!$this->getRequest()->getParam('k') || $this->getRequest()->getParam('k') != $helper->getConfigData('flint_feefo/general/key'))){
            $this->_forward('404');
            return;
        }

       if(!$helper->isIpAllowed()){
           $this->_forward('404');
           return;
       }

       if ($this->getRequest()->getParam('storecode') && !Mage::app()->getStore($this->getRequest()->getParam('storecode'))) {
           $this->_forward('404');
           return;
       }

        
       $this->Dom = new DOMDocument();
       $this->Dom->encoding = 'utf-8';
       
       
       if($this->getRequest()->getParam('start')) 
               $from = date('Y-m-d 00:00:00',  strtotime($this->getRequest()->getParam('start')));
       else $from = date('Y-m-d 00:00:00');
       if($this->getRequest()->getParam('end')) 
               $to = date('Y-m-d 23:59:59',  strtotime($this->getRequest()->getParam('end')));
       else $to = date('Y-m-d 23:59:59');
       
       $doc = $this->node('Items');
       foreach($helper->getOrdersCollection($from, $to) as $order){
            foreach($order->getAllVisibleItems() as $item){
                
                $orderItem = $this->node('Item');
                    $orderItem->appendChild($this->node('Name', $item->getOrder()->getCustomer_firstname()." ".$item->getOrder()->getCustomer_lastname()));
                    $orderItem->appendChild($this->node('Email',$item->getOrder()->getCustomer_email()));
                    $orderItem->appendChild($this->node('Date',$helper->getXmlTimeFormat($item->getOrder()->getCreated_at())));
                    $orderItem->appendChild($this->node('Feedback_Date',$helper->getFeedbackDay($order,$item)));
                    $orderItem->appendChild($this->node('Description',$item->getName()));
                    $orderItem->appendChild($this->node('Product_Search_Code',Mage::getModel('catalog/product')->load($item->getProduct_id())->getSku()));
                    $orderItem->appendChild($this->node('Order_Ref',$item->getOrder()->getIncrement_id()));
                    $orderItem->appendChild($this->node('Customer_Ref',$item->getOrder()->getCustomer_id()));
                    $orderItem->appendChild($this->node('Product_Link', Mage::getSingleton('catalog/product_url')
                                                                              ->getUrl(Mage::getModel('catalog/product')
                                                                                                    ->setStoreId($order->getStore()->getId())
                                                                                                    ->load($item->getProduct_id())
                                                                                             ,array('_store_to_url' => true))
                                                       )
                                           );
                    $orderItem->appendChild($this->node('Logon', $helper->getConfigData('flint_feefo/general/logon').'/'.$order->getStore()->getCode().'/'.$helper->getCategoryPath($item)));
            
            	$doc->appendChild($orderItem);  
            }
        } 
        
       $this->Dom->appendChild($doc);
       
       header('Content-Type: text/xml; charset=utf8');
       $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
      
       print $this->Dom->saveXML();  
    }
    
    private function node($name, $content = ''){
        $newElement = $this->Dom->createElement($name);
        if($content instanceof DOMElement){
            $newElement->appendChild($content);
        } elseif(strlen($content)){
            $newElement->appendChild($this->Dom->createTextNode( (string)$content));
        }
        return $newElement;
    }
    
}
