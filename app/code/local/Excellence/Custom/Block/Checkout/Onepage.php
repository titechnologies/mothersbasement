<?php
class Excellence_Custom_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage{

	public function getSteps()
	{
		$steps = array();

		if (!$this->isCustomerLoggedIn()) {
			$steps['login'] = $this->getCheckout()->getStepData('login');
		}

		//New Code Adding step excellence here
		//////////////////////////////////////////////////////////////////////////////// #1648
		/* ORGINAL
		$stepCodes = array('excellence','billing', 'shipping', 'shipping_method', 'payment','review');
		*/
		if(Mage::getSingleton('customer/session')->getId()){
			$stepCodes = array('billing', 'shipping', 'shipping_method', 'payment','review');
		}else{
			$stepCodes = array('excellence','billing', 'shipping', 'shipping_method', 'payment','review');
		}
		//////////////////////////////////////////////////////////////////////////////// #1648

		foreach ($stepCodes as $step) {
			$steps[$step] = $this->getCheckout()->getStepData($step);
		}
		return $steps;
	}

	//////////////////////////////////////////////////////////////////////////////// #1648
	/* ORIGINAL - 02-12-2013
		public function getActiveStep()
		{
			//New Code, make step excellence active when user is already logged in
			return $this->isCustomerLoggedIn() ? 'excellence' : 'login';
		}
	*/
	//////////////////////////////////////////////////////////////////////////////// #1648
}
