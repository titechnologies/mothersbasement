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


class FME_Layaway_Model_Period
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0','label' => Mage::helper('layaway')->__('No limit')),
            array('value' => '1','label' => Mage::helper('layaway')->__('Day')),
            array('value' => '2','label' => Mage::helper('layaway')->__('Week')),
            array('value' => '3','label' => Mage::helper('layaway')->__('Month')),
            array('value' => '4','label' => Mage::helper('layaway')->__('Year'))
        );
    }
    public function toOptionsArray()
    {
        return array(
                '0' => Mage::helper('layaway')->__('No limit'),
                '1' => Mage::helper('layaway')->__('Day'),
                '2' => Mage::helper('layaway')->__('Week'),
                '3' => Mage::helper('layaway')->__('Month'),
                '4' => Mage::helper('layaway')->__('Year')
        );
    }
}
