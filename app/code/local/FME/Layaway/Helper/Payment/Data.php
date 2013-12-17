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
 \\* @copyright  Copyright 2010 © free-magentoextensions.com All right reserved\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

class FME_Layaway_Helper_Payment_Data extends Mage_Payment_Helper_Data
{
	
	public function getStoreMethods($store=null, $quote=null)
	{
		$methods = parent::getStoreMethods($store, $quote);
		
		$check = Mage::helper('layaway')->getStoredDatafor('enable') && ($quote->getIsLayaway() || $quote->getLayawayOrder());
		$allowedmethods = explode(',',Mage::helper('layaway')->getStoredDatafor('allowedpaymethods'));
		if (!Mage::app()->getStore()->isAdmin() && $check && count($allowedmethods))
		{
			$tmp = array();
			
			foreach ($methods as $method)
			{
				if (!in_array($method->getCode(),$allowedmethods))
				{
					continue;
				}
				$tmp[] = $method;
			}
			$methods = $tmp;
		}
		return $methods;
	}
}