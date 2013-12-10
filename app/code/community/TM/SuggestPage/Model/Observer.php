<?php

class TM_SuggestPage_Model_Observer
{
    public function addToCartComplete(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('suggestpage/general/show_after_addtocart')) {
            return;
        }

        $observer->getResponse()->setRedirect(Mage::getUrl('suggest'));
        $session = Mage::getSingleton('checkout/session');
        $session->setNoCartRedirect(true);
        $session->setSuggestpageProductId($observer->getProduct()->getId());

        $message = Mage::helper('checkout')->__(
            '%s was added to your shopping cart.',
            Mage::helper('core')->htmlEscape($observer->getProduct()->getName())
        );
        $session->addSuccess($message);
    }
}
