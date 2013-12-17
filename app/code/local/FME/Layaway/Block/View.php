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

class FME_Layaway_Block_View extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('layaway/customer/view.phtml');
        $_order = $this->getOrder();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
	    ->addFieldToFilter('layaway_order',$_order->getId())
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->setOrder('created_at', 'desc')
        ;
        $this->setInstallments($orders);
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
        $pager = $this->getLayout()->createBlock('page/html_pager', 'layaway.installments.history.pager')
            ->setCollection($this->getInstallments());
        $this->setChild('pager', $pager);
        $this->getInstallments()->load();
        return $this;
    }
    
    public function getInstallmentViewUrl($order)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
    public function getInstallmentsPaid()
    {
        $_order = $this->getOrder();
        if($_order->getId()){
            $paid_orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('layaway_order',$_order->getId())
                ->getData();
            return count($paid_orders);
        }
        return 0;
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('*/*/index');
        }
        //return Mage::getUrl('*/*/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('layaway')->__('Back to %1s Orders',Mage::helper('layaway')->getLabelsStoredDatafor('layaway'));
        }
        //return Mage::helper('layaway')->__('View Another Order');
    }

    //public function getInvoiceUrl($order)
    //{
    //    return Mage::getUrl('*/*/invoice', array('order_id' => $order->getId()));
    //}
    //
    //public function getShipmentUrl($order)
    //{
    //    return Mage::getUrl('*/*/shipment', array('order_id' => $order->getId()));
    //}
    //
    //public function getCreditmemoUrl($order)
    //{
    //    return Mage::getUrl('*/*/creditmemo', array('order_id' => $order->getId()));
    //}

}
