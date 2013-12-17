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


class FME_Layaway_Model_Validator extends Mage_SalesRule_Model_Validator
{
    
    /**
     * Quote item discount calculation process
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Validator
     */
    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if($item->getSku()=='layaway_installment'){
            return $this;
        }
        return parent::process($item);
    }

    /**
     * Apply discounts to shipping amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Validator
     */
    public function processShippingAmount(Mage_Sales_Model_Quote_Address $address)
    {
        
        $quote              = $address->getQuote();
        $items = $address->getQuote()->getAllItems();
        
        foreach($items as $item){
            if($item->getSku()=='layaway_installment'){
                return $this;
            }
        }
        return parent::processShippingAmount($address);
    }

    public function initTotals($items, Mage_Sales_Model_Quote_Address $address)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            if($item->getSku()=='layaway_installment'){
                return $this;
            }
        }
        return parent::initTotals($items, $address);
    }
}
