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

class FME_Layaway_Block_Adminhtml_Layaway_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if (Mage::registry('current_order')) {
            return Mage::registry('current_order');
        }
        if (Mage::registry('order')) {
            return Mage::registry('order');
        }
        Mage::throwException(Mage::helper('sales')->__('Cannot get the order instance.'));
    }

  public function __construct()
  {
      parent::__construct();
      $this->setId('layaway_order_view_tabs');
      $this->setDestElementId('sales_order_view');
      $this->setTitle(Mage::helper('layaway')->__('%1s Order Information',Mage::helper('layaway')->getLabelsStoredDatafor('layaway')));
  }

  //protected function _beforeToHtml()
  //{
  //    $this->addTab('form_section', array(
  //        'label'     => Mage::helper('layaway')->__('Layaway Information'),
  //        'title'     => Mage::helper('layaway')->__('Layaway Information'),
  //        'content'   => $this->getLayout()->createBlock('layaway/adminhtml_layaway_edit_tab_layaway')->setTemplate('layaway/sales/order/detail.phtml')->toHtml(),
  //    ));
  //    
  //    
  //   
  //    return parent::_beforeToHtml();
  //}
}