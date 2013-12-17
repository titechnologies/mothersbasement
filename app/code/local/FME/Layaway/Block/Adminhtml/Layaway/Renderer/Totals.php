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

class FME_Layaway_Block_Adminhtml_Layaway_Renderer_Totals extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($this->getColumn()->getIndex()=='base_total_paid'){
	    $basetotal = $row->getData('base_grand_total');
	    $baseremaining = $row->getData('base_layaway_remaining');
	    $grandtotal =  Mage::app()->getLocale()->currency($row->getData('base_currency_code'))->getSymbol() . sprintf('%.2F',round($basetotal + $baseremaining,2));
	    return $grandtotal;
        }
        else if($this->getColumn()->getIndex()=='total_paid') {
            $total = $row->getData('grand_total');
	    $remaining = $row->getData('layaway_remaining');
	    $grandtotal =  Mage::app()->getLocale()->currency($row->getData('order_currency_code'))->getSymbol() . sprintf('%.2F',round($total + $remaining,2));
	    return $grandtotal;
        }
    }
}
?>