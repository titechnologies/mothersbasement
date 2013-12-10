<?php
class Excellence_Custom_Block_Checkout_Onepage_Excellence extends Mage_Checkout_Block_Onepage_Abstract{
	protected function _construct()
	{
		$this->getCheckout()->setStepData('excellence', array(
            'label'     => Mage::helper('checkout')->__('Have you been referred to us by an existing customer?'),
            'is_show'   => $this->isShow()
		));
		if ($this->isCustomerLoggedIn()) {
			$this->getCheckout()->setStepData('excellence', 'allow', true);
			$this->getCheckout()->setStepData('billing', 'allow', false);
		}

		parent::_construct();
	}
}
