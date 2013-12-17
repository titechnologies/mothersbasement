<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\   FME Layaway extension  \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   FME                            ///////
 \\\\\\\                      * @package    FME_Layaway                    \\\\\\\
 ///////    * @author     Malik Tahir Mehmood <malik.tahir786@gmail.com>   ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\* @copyright  Copyright 2010 � free-magentoextensions.com All right reserved\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

class FME_Layaway_Model_Creditmemo_Layawayfee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        $order = $creditmemo->getOrder();
        if ($order->getIsLayaway()) {
	    $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $order->getLayawayFee());
	    $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $order->getBaseLayawayFee());
        }
        return $this;
    }
    
}