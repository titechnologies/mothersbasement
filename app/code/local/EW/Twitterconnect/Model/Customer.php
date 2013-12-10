<?php
class EW_Twitterconnect_Model_Customer extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('twitterconnect/twitterconnect');
	}
	public function checkCustomer($data)
	{
		if(($screen = $data->screen_name) != null){
			$collection = Mage::getSingleton('customer/customer')->getCollection();
			$collection->addAttributeToFilter('ew_twitter_screen',$screen);
			if(count($collection)){
				foreach($collection as $customer){
					$customer = Mage::getModel('customer/customer')->load($customer->getId());
					if($customer){
						$this->_getSession()->setCustomerAsLoggedIn($customer);
						return true;
					}
				}
				return true;
			}
			return $this->createnewCustomer($data);
		}
		return false;
	}
	protected function _getSession()
	{
		return Mage::getSingleton( 'customer/session' );
	}
	public function createnewCustomer($data)
	{
		try{
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();
			$passwordLength = 10;
			
			$newcustomer = Mage::getModel('customer/customer');
			$customerData = array('website_id'=>$websiteId,
					"store"=>$store,
					"firstname"=>$data->name,
					"lastname"=>" ",
					"email"=>$data->email,
					"ew_twitter_screen"=>$data->screen_name
				);
			$newcustomer->setData($customerData);
			$newcustomer->setPassword($newcustomer->generatePassword($passwordLength));
			if($newcustomer->save()){
				if ($newcustomer->isConfirmationRequired()) {
					$newcustomer->sendNewAccountEmail('confirmed', '', $store->getId());
				}
				else{
					$newcustomer->sendNewAccountEmail('registered', '', $store->getId());
				}
				$newcustomer->sendPasswordReminderEmail();
				$this->_getSession()->setCustomerAsLoggedIn($newcustomer);
				return true;
			}
			throw new Exception("Unable to create new customer.");
		}
		catch(Exception $e){
			Mage::log($e->getMessage(),null,'ew_twitterconnect.log');
		}
		return false;
	}
} 
?>