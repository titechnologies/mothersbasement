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
class Flint_FeeFo_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getOrdersCollection($from = false, $to = false){
        $ordersStatus = Mage::getResourceModel('sales/order_status_history_collection') 
                ->addFieldToSelect('*')
                ->addFieldToFilter('status', array('in' => explode(',',$this->getConfigData('flint_feefo/general/queryDate'))))
                ->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to))
                ->setOrder('entity_id', 'asc')
                ->load();
        $ordersId = array();
        foreach ($ordersStatus as $orderStatus){
            $orderStatuses = Mage::getResourceModel('sales/order_status_history_collection') 
                ->addFieldToFilter('parent_id', array('in' => $orderStatus->getParent_id()))
                ->addFieldToFilter('status', array('in' => explode(',',$this->getConfigData('flint_feefo/general/queryDate'))))
                ->setOrder('entity_id', 'asc')
                ->load();
            if($orderStatuses->getFirstItem()->getEntity_id() == $orderStatus->getEntity_id())
                $ordersId[] = $orderStatus->getParent_id(); 
        }       
        $orders = Mage::getResourceModel('sales/order_collection')
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $ordersId)) 
                    ->addFieldToFilter('store_id',$this->getStore()->getId())
                    ->setOrder('created_at', 'asc');     
        
        return $orders;
    }
    
    public function getFeedbackDay($order,$item){
        if(!$this->getConfigData('flint_feefo/general/queryDateOffset')) return '';
        $queryDate = $this->getConfigData('flint_feefo/general/queryDate');
        $queryOffset = $this->getConfigData('flint_feefo/general/queryDateOffset');
        $date = $order->getCreated_at();
        return date("d-m-Y",  strtotime($date) + $queryOffset*24*60*60);
    }
    
    public function getXmlTimeFormat($date){
        return date("d-m-Y",  strtotime($date));
    }
    
    public function getProduct($id){
        return Mage::getModel('catalog/product')->load($id);
    }
    
    public function getCategoryPath($item){
        $path = array();
        foreach(Mage::getModel('catalog/product')->setStoreId($this->getStore()->getId())->load($item->getProduct_id())->getCategoryCollection() as $category){
           foreach(array_reverse($category->getParentCategories()) as $parentCategory ){
               $path[] = $parentCategory->getName();
           }
           break;
       }
       return implode('/', $path);
    }
    
    
    public function getLogolink($page = ""){
        switch ($page) {
            case 'product':
                $product = Mage::registry('current_product');

                return Mage::getModel('core/url')->getUrl('feefo/popup', array(
                    'logon' => Mage::getStoreConfig('flint_feefo/general/logon'),
                    'vendorref' => str_replace('/',";;47;;", $product->getSku()),
                    'mode' => Mage::getStoreConfig('flint_feefo/product/mode'),
                    'forfeedback' => Mage::getStoreConfig('flint_feefo/product/forfeedback'),
                    'order' => Mage::getStoreConfig('flint_feefo/product/order'),
                    'since' => Mage::getStoreConfig('flint_feefo/product/since'),                 
                    'limit' => Mage::getStoreConfig('flint_feefo/product/limit'),                 
                )).'?'.Mage::getStoreConfig('flint_feefo/product/additional');
                break;
            case 'category':
                foreach(array_reverse(Mage::registry('current_category')->getParentCategories()) as $parentCategory ){
                    $path[] = $parentCategory->getName();
                }
                return Mage::getModel('core/url')->getUrl('feefo/popup', array(
                    'logon' =>Mage::getStoreConfig('flint_feefo/general/logon'),//.'$'.implode('$', $path),
                    'mode' =>Mage::getStoreConfig('flint_feefo/category/mode'),
                    'forfeedback' => Mage::getStoreConfig('flint_feefo/category/forfeedback'),
                    'order' => Mage::getStoreConfig('flint_feefo/category/order'),
                    'since' => Mage::getStoreConfig('flint_feefo/category/since'),                 
                    'limit' => Mage::getStoreConfig('flint_feefo/category/limit'),                 
                )).'?'.Mage::getStoreConfig('flint_feefo/category/additional');
                break;
            case 'productpage':
                return Mage::getModel('core/url')->getUrl('feefo/popup', array(
                    'logon' => Mage::getStoreConfig('flint_feefo/general/logon'),
                    'mode' => Mage::getStoreConfig('flint_feefo/productpage/mode'),
                    'forfeedback' => Mage::getStoreConfig('flint_feefo/productpage/forfeedback'),
                    'order' => Mage::getStoreConfig('flint_feefo/productpage/order'),
                    'since' => Mage::getStoreConfig('flint_feefo/productpage/since'),
                    'limit' => Mage::getStoreConfig('flint_feefo/productpage/limit'),
                )).'?'.Mage::getStoreConfig('flint_feefo/productpage/additional');
                break;
            default:
                return Mage::getModel('core/url')->getUrl('feefo/popup', array(
                    'logon' =>Mage::getStoreConfig('flint_feefo/general/logon'),
                    'mode' =>Mage::getStoreConfig('flint_feefo/homepage/mode'),
                    'forfeedback' => Mage::getStoreConfig('flint_feefo/homepage/forfeedback'),
                    'order' => Mage::getStoreConfig('flint_feefo/homepage/order'),
                    'since' => Mage::getStoreConfig('flint_feefo/homepage/since'),                 
                    'limit' => Mage::getStoreConfig('flint_feefo/homepage/limit'),                 
                )).'?'.Mage::getStoreConfig('flint_feefo/homepage/additional');
        }
    }
    
    public function getLogoSrc($page = ""){
        switch ($page) {
            case 'product':
                $product = Mage::registry('current_product');
                return 'https://www.feefo.com/feefo/feefologo.jsp?'
                        .'logon='.Mage::getStoreConfig('flint_feefo/general/logon')
                        .'&template='.$this->getLogoTemplate($page)
                        .'&vendorref='.$product->getSku()
                        .Mage::getStoreConfig('flint_feefo/product/additional')
                ;

                break;
            case 'category':
                foreach(array_reverse(Mage::registry('current_category')->getParentCategories()) as $parentCategory ){
                    $path[] = $parentCategory->getName();
                }
                return 'https://www.feefo.com/feefo/feefologo.jsp?'
                        .'logon='.Mage::getStoreConfig('flint_feefo/general/logon')//.'/'.implode('/', $path)
                        .'&template='.$this->getLogoTemplate($page)
                        .Mage::getStoreConfig('flint_feefo/category/additional')
                ;

                break;
            case 'productpage':
                return 'https://www.feefo.com/feefo/feefologo.jsp?'
                        .'logon='.Mage::getStoreConfig('flint_feefo/general/logon')
                        .'&template='.$this->getLogoTemplate($page)
                        .Mage::getStoreConfig('flint_feefo/productpage/additional')
                ;
                break;
            default:
                return 'https://www.feefo.com/feefo/feefologo.jsp?'
                        .'logon='.Mage::getStoreConfig('flint_feefo/general/logon')
                        .'&template='.$this->getLogoTemplate($page)
                        .Mage::getStoreConfig('flint_feefo/homepage/additional')
                ;
        }
    }
    
    public function getStarsSrc($product = null){
        $src = 'https://www.feefo.com/feefo/feefologo.jsp?'
                .'logon='.Mage::getStoreConfig('flint_feefo/general/logon')
                .'&template='.Mage::getStoreConfig('flint_feefo/product/logoTemplate')
            ;
        if($product){
            $src .= '&vendorref='.$product->getSku();
        }elseif($this->_getRequest()->getParam('vendorref')){
            $src .= '&vendorref='.str_replace(";;47;;", '/',$this->_getRequest()->getParam('vendorref'));
        }
        $src .= Mage::getStoreConfig('flint_feefo/product/additional');
        
        return $src;
    }
    
    private function getLogoTemplate($page = ""){
        if($page && Mage::getStoreConfig('flint_feefo/'.$page.'/logoTemplate')){
            return Mage::getStoreConfig('flint_feefo/'.$page.'/logoTemplate');
        } else{
            return Mage::getStoreConfig('flint_feefo/general/logoTemplate');
        }
    }
    
    public function getProductXMLLink($product){
        $link = 'http://www.feefo.com/feefo/xmlfeed.jsp?';
        
        if(Mage::getStoreConfig('flint_feefo/general/logon')){
            $link .= '&logon='.Mage::getStoreConfig('flint_feefo/general/logon');
        }
        if(Mage::getStoreConfig('flint_feefo/product/mode')){
            $link .= '&mode='.Mage::getStoreConfig('flint_feefo/product/mode');
        }
        if($product->getSku()){
            $link .= '&vendorref='.$product->getSku();
        }
        if(Mage::getStoreConfig('flint_feefo/product/forfeedback')){
            $link .= '&forfeedback='.Mage::getStoreConfig('flint_feefo/product/forfeedback');
        }
        if(Mage::getStoreConfig('flint_feefo/product/order')){
            $link .= '&order='.Mage::getStoreConfig('flint_feefo/product/order');
        }
        if(Mage::getStoreConfig('flint_feefo/product/since')){
            $link .= '&since='.Mage::getStoreConfig('flint_feefo/product/since');
        }
        if(Mage::getStoreConfig('flint_feefo/product/limit')){
            $link .= '&limit='.Mage::getStoreConfig('flint_feefo/product/limit');
        }
        if(Mage::getStoreConfig('flint_feefo/product/additional')){
            $link .= Mage::getStoreConfig('flint_feefo/product/additional');
        }
        

        
       //print($link);
        return $link;
    }
    
     public function getXMLLink($product = false){
        if($product) 
            return $this->getProductXMLLink($product); 
        
        $link = 'http://www.feefo.com/feefo/xmlfeed.jsp?';
        
        if($this->_getRequest()->getParam('logon')){
            $link .= '&logon='.str_replace('$', '/', $this->_getRequest()->getParam('logon'));
        }
        if($this->_getRequest()->getParam('mode')){
            $link .= '&mode='.$this->_getRequest()->getParam('mode');
        }
        if($this->_getRequest()->getParam('vendorref')){
            $link .= '&vendorref='.str_replace(";;47;;", '/', $this->_getRequest()->getParam('vendorref'));
        }
        if($this->_getRequest()->getParam('forfeedback')){
            $link .= '&forfeedback='.$this->_getRequest()->getParam('forfeedback');
        }
        if($this->_getRequest()->getParam('order')){
            $link .= '&order='.$this->_getRequest()->getParam('order');
        }
        if($this->_getRequest()->getParam('since')){
            $link .= '&since='.$this->_getRequest()->getParam('since');
        }
        if($this->_getRequest()->getParam('limit')){
            $link .= '&limit='.$this->_getRequest()->getParam('limit');
        }
        if (Mage::getStoreConfig('flint_feefo/product/additional')) {
            $link .= Mage::getStoreConfig('flint_feefo/product/additional');
        }
        

        return $link;
    }
    
      
    public function getReviews($product = false){
        $cacheLiveTime = Mage::getStoreConfig('flint_feefo/general/caching');
        $url = $this->getXMLLink($product);

        $reviews = Mage::app()->loadCache($url);
        
        
        if (!$reviews || !$cacheLiveTime) {
            if(!$reviews = $this->loadReviewsXML($url)){
                return false;
            }
            $xmlstr = $reviews->saveXML();
            Mage::app()->saveCache($xmlstr, $url, array('FLINT_FEEFO'), Mage::getStoreConfig('flint_feefo/general/caching'));
            return $reviews;
        }else{
            $dom = new DOMDocument();
            $dom->loadXML($reviews);
            return $dom;
        }
    }
    
    private function loadReviewsXML($url){
        $dom = new DOMDocument();
        if(!$dom->load($url)){
            return 0;
        }
        return $dom;
    }
    
    public function isIpAllowed()
    {
        $allow = true;

        $allowedIps = $this->getConfigData('flint_feefo/general/firewall');
        if (!empty($allowedIps)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search(Mage::helper('core/http')->getRemoteAddr(), $allowedIps) === false) {
                $allow = false;
            }
        }

        return $allow;
    }

    public function getStore(){
        try {
            if($this->_getRequest()->getParam('storecode')) 
                return Mage::app()->getStore($this->_getRequest()->getParam('storecode'));
        } catch (Exception $exc) {
            
        }
        return Mage::app()->getStore();
    }
    
    public function getConfig() {
        return Mage::getSingleton('flint_feefo/config');
    }
    
    public function getConfigData($path){
        return $this->getStore()->getConfig($path);
    }

}
