<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Model/Observer.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ IMCecESwgDpUajgk('506ce28b700f73ccb65c5b7c0bba9dff'); ?><?php
/**
 * @copyright  Copyright (c) 2010 AITOC, Inc.
 */
class Aitoc_Aitpreorder_Model_Observer
{
    public function onSalesQuoteItemQtySetAfter($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();

        $simpleProduct=$quoteItem->getProduct()->getCustomOption('simple_product');
        if(isset($simpleProduct))
        {
            $SimpleId=$simpleProduct->getProduct()->getId();
            $_product = Mage::getModel('catalog/product')->load($SimpleId);
        }
        else
        {
            $_product = Mage::getModel('catalog/product')->load($quoteItem->getProduct()->getId());
        }
        $preorder=$_product->getPreorder();
        if($preorder=='1')
        {
            $comma=", ";
            if($_product->getPreorderdescript()=="")
            {
                $comma="";
            }
            $preordermsg=Mage::helper('aitpreorder')->__('Pre-Order').$comma.$_product->getPreorderdescript();
        }
        if(isset($preordermsg))
        {
            if($quoteItem->getMessage()== "")
            {
                $quoteItem->setMessage($preordermsg);
            }
        }
    }

    public function onSalesOrderSaveAfter($observer)
    {

        if(!Mage::registry("aitoc_inside_order"))
        {
            $order              =   $observer->getOrder();
            $orderStatusNormal  =   $order->getStatus();
            $orderStatus        =   $order->getStatusPreorder();

            if(!$orderStatus)
            {
                    $orderStatus    =   $orderStatusNormal;
            }

            if(($orderStatus=='processingpreorder')||($orderStatus=='processing')||($orderStatus=='complete'))
            {
                $_items=$order->getItemsCollection();
                $haveregular    =   0;
                $haveregular    =   Mage::helper('aitpreorder')->isHaveReg($_items,0);

                if(($haveregular==1)&&($orderStatus=='processingpreorder')&&($orderStatus!='processing'))
                {
                    $order->setData('status_preorder','processing');
                    $order->setData('status','processing');
                    $order->addStatusHistoryComment('','processing');
                }
                elseif(($haveregular==0)&&($orderStatus=='processing')&&($orderStatus!='processingpreorder'))
                {
                    $order->setData('status_preorder','processingpreorder');
                    $order->setData('status','processing');
                    $order->addStatusHistoryComment('','processingpreorder');
                }
                elseif(($haveregular==-2)&&($orderStatus!='complete')&&($orderStatus!='processing'))
                {
                    $order->setData('status_preorder','complete');
                    $order->setData('status','complete');
                    $order->addStatusHistoryComment('','complete');
                }

                if(($haveregular==-1)&&($orderStatus!='processingpreorder'))
                {
                    $order->setData('status_preorder','processingpreorder');
                    $order->setData('status','processing');
                    $isCustomerNotified=true;
                    $order->addStatusHistoryComment('','processingpreorder');
                }
            }
            elseif(($orderStatus=='pending')||($orderStatus=='pendingpreorder'))
            {
                $haveregular=0;
                $_items=$order->getItemsCollection();
                $haveregular=Mage::helper('aitpreorder')->isHaveReg($_items,1);
                if(($haveregular==0)&&($orderStatus=='pending'))
                {
                    $order->setData('status_preorder','pendingpreorder');
                    $order->setData('status','pending');
                    $order->addStatusHistoryComment('','pendingpreorder');
                }
                elseif(($haveregular!=0)&&($orderStatus=='pendingpreorder'))
                {
                    $order->setData('status_preorder','pending');
                    $order->setData('status','pending');
                    $isCustomerNotified=true;
                    $order->addStatusHistoryComment('','pending');
                }
            } else {

            $order->setData('status_preorder',$orderStatus);

            }

            Mage::register("aitoc_inside_order",1);
            $order->save();
            Mage::unregister("aitoc_inside_order");
        }
    }

    public function onBundleProductViewConfig($observer)
    {
        $observer['selection']->getId();
        $opts=array();

        $product = Mage::getModel('catalog/product');
        $product->load($observer['selection']->getId());
        if($product->getPreorder()=='1')
        {
            $opts['ispreorder']=1;
            $opts['preorderdescript']=Mage::helper('aitpreorder')->__('Pre-Order');
        }
        else
        {
            $opts['ispreorder']=0;
        }

        $observer['response_object']->setAdditionalOptions($opts);

    }

    public function onCatalogProductSaveAfter($observer)
    {
            $event = $observer->getEvent();

            $product = $event->getProduct();

            $preorderDataOrig =$product->getOrigData('preorder');
            $preorderData = $product->getData('preorder');
            //usual - > preorder: $preorderDataOrig = 0 ; preorderData = 1
            //preorder - > usual: $preorderDataOrig = 1 ; preorderData = 0    !!!!!!!!
            $needAlert = false;
            if ($preorderDataOrig!=$preorderData)
            {
                $coll = Mage::getResourceModel('sales/order_collection');
                /* @var $coll Mage_Sales_Model_Mysql4_Order_Collection */
                if($preorderData!=1)
                {
                    $needAlert = true;
                    $statuses[]='processingpreorder';
                    $statuses[]='pendingpreorder';
                }
                else
                {
                    $statuses[]='processing';
                    $statuses[]='pending';
                }


                $coll->addFieldToFilter('status_preorder',$statuses);
                /* @var $resource Mage_Sales_Model_Mysql4_Order */

                $versionInfo = Mage::getVersionInfo();
                if (version_compare(Mage::getVersion(),'1.4.1.0','>='))
                {
                    $prealias='main_table';
                }
                else
                {
                    $prealias='e';
                }

                $coll->getSelect()
                        ->join(array('oi' => $coll->getTable('sales/order_item')), 'oi.order_id = '.$prealias.'.entity_id', array('oi.product_id', 'oi.sku'))
                        ->where('oi.product_id = ?', $product->getId())
                        ->group($prealias.'.entity_id');

                foreach($coll->getItems() as $order)
                {
                    /* @var $order Mage_Sales_Model_Order */

                    ///method for sending email


                    if($needAlert&&$order->getData('customer_id')&&Mage::getStoreConfig('catalog/aitpreorder/send_email'))
                    {
                        $url = Mage::getModel('core/url')->getUrl('sales/order/view', array('order_id' => $order->getId()));
                        Mage::unregister('order_view_url');
                        Mage::register('order_view_url', $url);
                        $this->sendAlert($order, $product);
                    }

                    $tmp = new Varien_Object(array('order'=>$order));
                    self::onSalesOrderSaveAfter($tmp);
                }

            }
    }

    public function sendAlert($order, $product)
    {
        $customer_id = $order->getData('customer_id');
        $configSendIfNotPreorder = true;
        if($customer_id&&$configSendIfNotPreorder)
        {
            $email = Mage::getModel('aitpreorder/email');
            try {
                $website = Mage::getModel('core/store')->load($order->getData('store_id'))->getWebsite();
                $email->setWebsite($website);
                $customer = Mage::getModel('customer/customer')->load($customer_id);
                $product->setCustomerGroupId($customer->getGroupId());
                $email->addStockProduct($product);
                $email->setType('stock');
                $email->setCustomer($customer);
                $email->send();
                $email->clean();

            }
            catch (Exception $e) {
                Mage::log($e->getMessage());
            }

        }

    }


    public function beforeCatalogProductCollectionLoad($observer)
    {
        if (!Mage::app()->getStore()->isAdmin())
        {
            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
            /* */
            $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitpreorder')->getLicense()->getPerformer();
            $ruler = $performer->getRuler();
            if (!($ruler->checkRule('store',Mage::app()->getStore()->getId(),'store') || $ruler->checkRule('store',Mage::app()->getStore()->getWebsiteId(),'website')))
            {
                    $collection = $observer->getCollection();
                    $collection->addAttributeToFilter('preorder',array(array('null'=>1),array('eq'=>0)), 'left');
            }
            /* */
        }
    }

    public function onCatalogProductLoad($observer)
    {
        if (!Mage::app()->getStore()->isAdmin())
        {
            /* @var $product Mage_Catalog_Model_Product */
            /* */
            $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitpreorder')->getLicense()->getPerformer();
            $ruler = $performer->getRuler();
            if (!($ruler->checkRule('store',Mage::app()->getStore()->getId(),'store') || $ruler->checkRule('store',Mage::app()->getStore()->getWebsiteId(),'website')))
            {
                    $product = $observer->getProduct();
                    if($product->getPreorder()==1)
                    {
                        $product->unsetData();
                    }
            }
            /* */
        }
    }
//     public function onSalesOrderLoad($observer)
//     {
//         $order = $observer->getEvent()->getOrder();
//         if(!$order->getId())
//         {
//             return;
//         }
//         if(!Mage::helper('aitpreorder')->checkSynchronization($order->getStatus(),$order->getStatusPreorder()))
//         {
//             $order->setStatusPreorder($order->getStatus());
//             $order->save();
//         }
//         $order->setStatus($order->getStatusPreorder());
//     }

    public function onSalesOrderLoad($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if(!$order->getId())
        {
            return;
        }
        if(!Mage::helper('aitpreorder')->checkSynchronization($order->getStatus(),$order->getStatusPreorder()))
        {
             $order->setStatusPreorder($order->getStatus());
            //$order->save();
        }
        $order->setStatus($order->getStatusPreorder());

    }

    }
}
