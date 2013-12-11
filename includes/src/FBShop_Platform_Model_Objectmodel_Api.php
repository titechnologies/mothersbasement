<?php
class FBShop_Platform_Model_ObjectModel_Api extends Mage_Api_Model_Resource_Abstract
{
      public function methodName($arg)
    {
        return "Hello World! My argument is : " . $arg;
    }
    public function setCart($arg){
       Mage::getSingleton('core/session')->setCartValue("masha");
   
    }
    public function getCart($arg){
       
       return Mage::getSingleton('core/session')->getCartValue();
        }
        
   public function   addCostomer ($customerData){
   
       try {
            $customer = Mage::getModel('customer/customer')
                ->setData($customerData)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            // We cannot know all the possible exceptions,
            // so let's try to catch the ones that extend Mage_Core_Exception
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $customer->getId();
   }
  public function getOrderInfo($arg){
      
       $order = Mage::getModel('sales/order')->load($arg['id']);
      // return $order->getCustomerEmail();
    if ($order->getCustomerEmail()==$arg['customerEmail'])
       $json="[{'id' : ".$order->getId().",
             'incrementId': ".$order->getIncrementId().",
             'status': ".$order->getStatus()."}]";
       
   return $json;
//    }
//   else 
//       return 'Invalid Id';
  }
}
?>