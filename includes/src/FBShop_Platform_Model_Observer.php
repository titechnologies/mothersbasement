<?php

class FBShop_Platform_Model_Observer {

    public function removeQuoteItem(Varien_Event_Observer $observer) {

        $event = $observer->getEvent();  //Fetches the current event
        $product = $observer->getQuoteItem()->getProduct();

        $info = $product->getTypeInstance(true)->getOrderOptions($product);
        $json = array();
        $configurableAttributes = array();
        $customOption = array();
        if ((is_array($info["info_buyRequest"]["super_attribute"])) && ((!isset($info["info_buyRequest"]["super_attribute"][0])) || ($info["info_buyRequest"]["super_attribute"][0] != "0") || ($info["info_buyRequest"]["super_attribute"][0] != 0))) {
            foreach ($info["info_buyRequest"]["super_attribute"] as $key => $value)
                $configurableAttributes[] = array("optionExternalId" => $key, "optionValueExternalId" => $value);
        }
        if (is_array($info["info_buyRequest"]["options"]) && ((!isset($info["info_buyRequest"]["options"][0])) || ($info["info_buyRequest"]["options"][0] != "0") || ($info["info_buyRequest"]["options"][0] != 0))) {
            foreach ($info["info_buyRequest"]["options"] as $key => $value)
                $customOption[] = array("optionExternalId" => $key, "optionValueExternalId" => $value);
        }
        $json = array("productId" => $product->getId(),
            "configurableAttributes" => $configurableAttributes,
            "customOption" => $customOption);


        $json = json_encode($json);
        $model = Mage::getModel('platform/platform')->loadShop();
        if ($model!=false)
        if (!empty($model)){
        $host = $model->getHost();
        $token = Mage::getSingleton('core/session')->getToken();
        $shopName = $model->getShop();
        }
// echo Mage::getSingleton('checkout/onepage/success')->addSuccess($host.'/'.$shopName.'/cart/remove/'.$token.'/'.$product->getId());
        if (isset($host) && isset($token) && isset($shopName)) {

            try {
                $var = new Mage_HTTP_Client_Curl();
                $var->post($host . '/' . $shopName . '/cart/remove/' . $token, array('cart' => $json));
            } catch (Exception $e) {
                
            }
//  echo Mage::getSingleton('checkout/session')->addSuccess($host.'/'.$shopName.'/cart/remove/'.$token.'/'.$product->getId());
            if ($var) {
//  echo Mage::getSingleton('checkout/session')->addSuccess("Succesuful! Remove");
            } else {
                Mage::getSingleton('core/session')->unsHost();
                Mage::getSingleton('core/session')->unsToken();
                Mage::getSingleton('core/session')->unsShopName();
            }
        }
//    echo Mage::getSingleton('checkout/session')->addSuccess("Succesuful! Removeddddddddddd");

        return;
    }

    public function updateCartItems(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $items = $observer->getCart()->getQuote()->getAllVisibleItems();



         $model = Mage::getModel('platform/platform')->loadShop();
         if ($model!=false)
        if (!empty($model)){
        $host = $model->getHost();
        $token = Mage::getSingleton('core/session')->getToken();
        $shopName = $model->getShop();
        }
        
              
     

        $json = array();
        foreach ($items as $item) {

            $product = $item->getProduct();
            $info = $product->getTypeInstance(true)->getOrderOptions($product);

            $configurableAttributes = array();
            $customOption = array();
            if ((is_array($info["info_buyRequest"]["super_attribute"])) && ((!isset($info["info_buyRequest"]["super_attribute"][0])) || ($info["info_buyRequest"]["super_attribute"][0] != "0") || ($info["info_buyRequest"]["super_attribute"][0] != 0))) {
                foreach ($info["info_buyRequest"]["super_attribute"] as $key => $value)
                    $configurableAttributes[] = array("optionExternalId" => $key, "optionValueExternalId" => $value);
            }
            if (is_array($info["info_buyRequest"]["options"]) && ((!isset($info["info_buyRequest"]["options"][0])) || ($info["info_buyRequest"]["options"][0] != "0") || ($info["info_buyRequest"]["options"][0] != 0))) {
                foreach ($info["info_buyRequest"]["options"] as $key => $value)
                    $customOption[] = array("optionExternalId" => $key, "optionValueExternalId" => $value);
            }


            $json[] = array("qty" => $item->getQty(),
                "productId" => $item->getProductId(),
                "configurableAttributes" => $configurableAttributes,
                "customOption" => $customOption);
        }

        $json = json_encode($json);

        if (isset($host) && isset($token) && isset($shopName)) {
            try {
                $var = new Mage_HTTP_Client_Curl();
                $url=$host . '/' . $shopName . '/cart/update/' . $token;
                $var->post($url, array('products' => $json));
            } catch (Exception $e) {
                
            }

            if ($var) {
//      echo Mage::getSingleton('checkout/session')->addSuccess("Succesuful! Update");
            } else {
                Mage::getSingleton('core/session')->unsHost();
                Mage::getSingleton('core/session')->unsToken();
                Mage::getSingleton('core/session')->unsShopName();
            }
        }
//  echo Mage::getSingleton('checkout/session')->addSuccess("Succesuful! update ");
    }

    public function addQuoteItem(Varien_Event_Observer $observer) {

        $event = $observer->getEvent();  //Fetches the current event
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();

        $info = $product->getTypeInstance(true)->getOrderOptions($product);

        $configurableAttributes = array();
        $customOption = array();
        if ((is_array($info["info_buyRequest"]["super_attribute"])) && ((!isset($info["info_buyRequest"]["super_attribute"][0])) || ($info["info_buyRequest"]["super_attribute"][0] != "0") || ($info["info_buyRequest"]["super_attribute"][0] != 0))) {
            foreach ($info["info_buyRequest"]["super_attribute"] as $key => $value)
                $configurableAttributes[] = array("optionExternalId" => $key, "optionValueExternalId" => $value);
        }
        if (is_array($info["info_buyRequest"]["options"]) && ((!isset($info["info_buyRequest"]["options"][0])) || ($info["info_buyRequest"]["options"][0] != "0") || ($info["info_buyRequest"]["options"][0] != 0))) {
            foreach ($info["info_buyRequest"]["options"] as $key => $value)
                $customOption[] = array("optionExternalId" => $key, "optionValueExternalId" => $value);
        }
        $json = array("qty" => $info["info_buyRequest"]['qty'],
            "productId" => $info["info_buyRequest"]['product'],
            "configurableAttributes" => $configurableAttributes,
            "customOption" => $customOption);

        $json = json_encode($json);
        $qty = $product->getQty();
        $eventmsg = "Current Event Triggered : <I>" . $event->getName() . "</I><br/> Currently Added Product : <I>" . $product->getId() . " </I>";

       $model = Mage::getModel('platform/platform')->loadShop();
       if ($model!=false)
        if (!empty($model)){
        $host = $model->getHost();
        $token = Mage::getSingleton('core/session')->getToken();
        $shopName = $model->getShop();
        }
        
        $add = Mage::getSingleton('core/session')->getAdd();
        if (isset($host) && isset($token) && isset($shopName)) {
//      echo Mage::getSingleton('checkout/session')->addSuccess("Succesuful! Add".$product->getId()."/".$qty);
             try {
                    $var = new Mage_HTTP_Client_Curl();
                    $url= $host . '/' . $shopName . '/cart/remove/'.$token;
                    $var->post($host . '/' . $shopName . '/cart/remove/' . $token, array('cart' => $json));
                } catch (Exception $e) {
                    
                }
                Mage::getSingleton('core/session')->unsAdd();
         
            try {
                $var = new Mage_HTTP_Client_Curl();
                $var->post($host . '/' . $shopName . '/cart/add/' . $token, array('cart' => $json));
            } catch (Exception $e) {
                
            }
        }
        return;
    }

    public function orderPaymentSucces(Varien_Event_Observer $observer) {

try {
          
    $returnUrl = Mage::getSingleton('core/session')->getReturnUrl();
              if (!empty($returnUrl)) {
                Mage::getSingleton('core/session')->unsReturnUrl();
                Mage::app()->getResponse()->setRedirect(trim(base64_decode($returnUrl)));
            }
        } catch (Exception $e){
        
    }
  }

    public function orderPaymentFailure($observer) {
//        try {
//            $event = $observer->getEvent();
//            $request = $event->getControllerAction()->getRequest();
//            if (($request->getRouteName() == "checkout") && ($request->getActionName() == "failure")) {
//
//                $host = Mage::getSingleton('core/session')->getHost();
//                $token = Mage::getSingleton('core/session')->getToken();
//                $shopName = Mage::getSingleton('core/session')->getShopName();
//                $facebookAppName = Mage::getSingleton('core/session')->getFacebookAppName();
//                $returnUrl = Mage::getSingleton('core/session')->getReturnUrl();
//
//
//                if (!empty($returnUrl)) {
//                    Mage::getSingleton('core/session')->unsReturnUrl();
//                    Mage::app()->getResponse()->setRedirect($returnUrl);
//                }
//                if (isset($host) || isset($token) || isset($shopName) || isset($facebookAppName)) {
//                    try {
////                $var = new Mage_HTTP_Client_Curl();
////            if(!$returnUrl)    $var->post($host . '/' . $shopName . '/cart/clear/' . $token);
//                    } catch (Exception $e) {
//                        
//                    }
//                    Mage::getSingleton('core/session')->unsHost();
//                    Mage::getSingleton('core/session')->unsToken();
//                    Mage::getSingleton('core/session')->unsShopName();
//                    Mage::getSingleton('core/session')->unsFacebookAppName();
//                    try {
//                        if ($returnUrl)
//                            $url = $returnUrl;
//
//                        Mage::app()->getResponse()->setRedirect($url);
//                    } catch (Exception $e) {
//                        
//                    }
//                }
//            }
//        } catch (Exception $e) {
//            
//        }
    }

 public function placeOrderAfter(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $order = $event->getOrder();
//        echo Mage::getSingleton('core/session')->addSuccess("Succesuful! update ");   
   $returnUrl = Mage::getSingleton('core/session')->getReturnUrl();  
   if (!empty($returnUrl)){
   $order->setStoreId(null);
   $order->setStoreName("Shopidoo");
   
   }
        foreach ($order->getItemsCollection() as $item) {
            try {
                 $model = Mage::getModel('platform/platform')->loadShop();
                if ($model!=false)
                if ($model->getId() != NULL) {
                    $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                    $url = $model->getHost() . '/events/' . $model->getShop() . '/save/' . $item->getProductId() . "?host=" . $host;
                    $var = new Mage_HTTP_Client_Curl();
                    $var->post($url);
                }
            } catch (Exception $e) {
                
            }
        }
//        $orderComment = Mage::getSingleton('core/session')->getOrderComment();
//        Mage::getSingleton('core/session')->unsOrderComment();
//        if (isset($orderComment)) {
//            $order->addStatusHistoryComment($orderComment);
//            $order->save();
//        }
    }

    public function orderSave(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $obj = $event->getOrder();
        if ($obj instanceof Mage_Sales_Model_Order) {
            $data = $obj->getData();

            try {
                   $model = Mage::getSingleton('platform/platform')->loadShop();
                   if ($model!=false)
                if ($model->getId() != NULL) {
                    $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                    $url = $model->getHost() . '/events/' . $model->getShop() . '/order/update/' . $data["quote_id"] . "?host=" . $host;
                    $var = new Mage_HTTP_Client_Curl();
                    $var->post($url);
                }
            } catch (Exception $e) {
                
            }
        }
    }

    public function deleteProduct(Varien_Event_Observer $observer) {

        try {
            $event = $observer->getEvent();
            $product = $event->getProduct();

//   echo Mage::getSingleton('adminhtml/session')->addSuccess('s'.$product->getId());


           $model = Mage::getSingleton('platform/platform')->loadShop();
           if ($model!=false)
            if ($model->getId() != NULL) {

                $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                $var = new Mage_HTTP_Client_Curl();
                $var->post($model->getHost() . '/events/' . $model->getShop() . '/delete/' . $product->getId() . "?host=" . $host);
            }
        } catch (Exception $e) {
            
        }
    }

    public function saveProduct(Varien_Event_Observer $observer) {
// print_r($observer);
        try {
            $event = $observer->getEvent();
            $product = $event->getProduct();
            $model = Mage::getSingleton('platform/platform')->loadShop();
            if ($model!=false)
            if ($model->getId() != NULL) {
                $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                $url = $model->getHost() . '/events/' . $model->getShop() . '/save/' . $product->getId() . "?host=" . $host;
                $var = new Mage_HTTP_Client_Curl();
                $var->post($url);
            }
        } catch (Exception $e) {
            
        }
    }

    public function message($observer) {
        try {
//            echo Mage::getSingleton('core/session')->addSuccess("Succesuful!");
//             $layout= Mage::getSingleton('core/layout');
//             $block = $layout->createBlock("Platform/notification_window");
////            adminhtml/notification_window
//              $block->setTemplate("notification/window.phtml");
//              
//              $layout->_addContent($block);
//              $layout->renderLayout();
//        if ($layout->getBlock('body')){ $layout->getBlock('content')->append($block); $p=true;}
//        if ($layout->getBlock('content')&&(!$p)) $layout->getBlock('content')->append($block);
        } catch (Exception $e) {
            
        }
    }

    public function test(Varien_Event_Observer $observer) {
//           $event = $observer->getEvent();
//        $product = $event->getProduct();
//     //   Mage::app()->getResponse()->setRedirect('FBShop/index/test?id='.$product->getId());
////      Mage::app()->_redirect('FBShop/index/test?id='.$product->getId());
//       
//        $url = "http://www.google.com";
//        var_dump($observer);
//        Mage::app()->getResponse()->setRedirect($url);
////    $this->_addContent($block);
////    $this->renderLayout();
//     echo Mage::getSingleton('core/session')->addSuccess("Succesuful!");    
    }

}

?>