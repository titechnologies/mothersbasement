<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Excellence_Custom_OnepageController extends  Mage_Checkout_OnepageController{

//////////////////////////////////////////////////////////////////////////////// #1648
    public function saveExcellenceAction(){
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('excellence', array());
            $result = $this->getOnepage()->saveExcellence($data);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'billing';
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
//////////////////////////////////////////////////////////////////////////////// #1648



//////////////////////////////////////////////////////////////////////////////// #1648
//save checkout billing address
// override of base function of checkout billing action in 'app/code/core/Mage/Checkout/controllers/OnepageController.php'
    public function saveBillingAction(){
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
		//$postData = $this->getRequest()->getPost('billing', array());
		//$data = $this->_filterPostData($postData);
		$data = $this->getRequest()->getPost('billing', array());
		$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

		if (isset($data['email'])) {
			$data['email'] = trim($data['email']);
		}

		//////////21-11-2013
/*
		// this session value set in 'app/code/local/Excellence/Custom/Model/Checkout/Type/Onepage.php'
		$referrelEmailId = Mage::getSingleton('checkout/session')->getQuote()->getExcellenceLike(); //getting referrel email id
		$data['rewards_referral'] = trim($referrelEmailId); //updating 'rewards_referral' in billing data array with actual referral email id
*/
		//////////21-11-2013


            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
//////////////////////////////////////////////////////////////////////////////// #1648
}
