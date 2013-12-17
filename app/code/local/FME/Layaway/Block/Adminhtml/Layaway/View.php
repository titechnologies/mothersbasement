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

class FME_Layaway_Block_Adminhtml_Layaway_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
     
	
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_blockGroup = 'layaway';
        $this->_controller = 'adminhtml_layaway';
	
        parent::__construct();
	$this->setId('sales_order_view');
	$this->_removeButton('save');
	
            $this->_addButton('view', array(
                'label'     => Mage::helper('sales')->__('View'),
                'onclick'   => 'setLocation(\'' . $this->getViewUrl() . '\')',
            ));
        
       
    }
    public function getViewUrl()
    {
	if( Mage::registry('layaway_data') && Mage::registry('layaway_data')->getId() ) {
	    return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>Mage::registry('layaway_data')->getId()));
	} 
    }
    public function getHeaderText()
    {
        if( Mage::registry('layaway_data') && Mage::registry('layaway_data')->getRealOrderId() ) {
            return Mage::helper('layaway')->__("%1s Order '%2s'",Mage::helper('layaway')->getLabelsStoredDatafor('layaway'), $this->htmlEscape(Mage::registry('layaway_data')->getRealOrderId()));
        } 
    }
}