<?php

/**
 * Send new order email to admin or specified email id
 * 
 * @author Mage Magician on 9 Oct 2012
 */

class Magemagician_Adminorderemail_Model_Observer
{
    /**
     * XML configuration paths
     */
    const XML_PATH_ADMINORDEREMAIL_TEMPLATE               = 'adminorderemail/adminorderemail/template';
    const XML_PATH_ADMINORDEREMAIL_GUEST_TEMPLATE         = 'adminorderemail/adminorderemail/guest_template';
    const XML_PATH_ADMINORDEREMAIL_IDENTITY               = 'adminorderemail/adminorderemail/identity';
    const XML_PATH_ADMINORDEREMAIL_COPY_TO                = 'adminorderemail/adminorderemail/copy_to';
    const XML_PATH_ADMINORDEREMAIL_ENABLED                = 'adminorderemail/adminorderemail/enabled';
	
	
    /**
     * Send email with order data
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendAdminNewOrderEmail(Varien_Event_Observer $observer)
    {
        /** @var $orderInstance Mage_Sales_Model_Order */
        $orderInstance = $observer->getOrder();
		
		$storeId = $orderInstance->getStore()->getId();

        if (!Mage::getStoreConfig(self::XML_PATH_ADMINORDEREMAIL_ENABLED, $storeId)) {
            return $this;
        }
		
        // Get the destination email addresses to send copies to
        $copyTo = array();
		$data = Mage::getStoreConfig(self::XML_PATH_ADMINORDEREMAIL_COPY_TO, $storeId);
        if (!empty($data)) {
            $copyTo = explode(',', $data);
        }
				

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($orderInstance->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($orderInstance->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_ADMINORDEREMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $orderInstance->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_ADMINORDEREMAIL_TEMPLATE, $storeId);
            $customerName = $orderInstance->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && is_array($copyTo)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_ADMINORDEREMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $orderInstance,
                'billing'      => $orderInstance->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        $orderInstance->setEmailSent(true);
    }
    
}