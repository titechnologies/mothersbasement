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
class FME_Layaway_Model_Observer_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the layaway_id refers to the key field in your database table.
        $this->_init('layaway/product', 'layaway_id');
    }
	
	/**
	 * Inject one tab into the product edit page in the Magento admin
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function injectTabs(Varien_Event_Observer $observer)
	{
		$block = $observer->getEvent()->getBlock();
		
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
			if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) {
				$block->addTab('layaway', array(
				    'before'=>'inventory',
					'label'     => Mage::helper('layaway')->getLabelsStoredDatafor('layaway'),
					'content'   => $block->getLayout()->createBlock('adminhtml/template', 'layaway-tab-content', array('template' => 'layaway/content.phtml'))->toHtml(),
				));
			}
		}
	}

	/**
	 * This method will run when the product is saved
	 * Use this function to update the product model and save
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function saveTabData(Varien_Event_Observer $observer)
	{
	    if ($post = $this->_getRequest()->getPost()) {
		try {
		    // Load the current product model
		    if($product = Mage::registry('product')){
		       // print_r($post);exit;
			if($post["layaway_id"] <= 0){
			    $productsArray = array();
			    $productsArray['product_id'] = $product["entity_id"];
			    $productsArray['layaway_status'] = isset($post["layaway_status"])?$post["layaway_status"]:$post["default_layaway_status"];
			    $productsArray['layaway_fees'] = isset($post["layaway_fees"])?$post["layaway_fees"]:$post["default_layaway_fees"];
			    $productsArray['layaway_first'] = isset($post["layaway_first"])?$post["layaway_first"]:$post["default_layaway_first"];
			    $productsArray['layaway_maxinstallments'] = isset($post["layaway_maxinstallments"])?$post["layaway_maxinstallments"]:$post["default_layaway_maxinstallments"];
			    $productsArray['config_layaway_status'] = isset($post["config_layaway_status"])?$post["config_layaway_status"]:0;
			    $productsArray['config_layaway_fees'] = isset($post["config_layaway_fees"])?$post["config_layaway_fees"]:0;
			    $productsArray['config_layaway_first'] = isset($post["config_layaway_first"])?$post["config_layaway_first"]:0;
			    $productsArray['config_layaway_maxinstallments'] = isset($post["config_layaway_maxinstallments"])?$post["config_layaway_maxinstallments"]:0;
			    $productsArray['layaway_period'] = isset($post["layaway_period"])?$post["layaway_period"]:$post["default_layaway_period"];
			    $productsArray['config_layaway_period'] = isset($post["config_layaway_period"])?$post["config_layaway_period"]:0;
			    $productsArray['layaway_period_frequency'] = isset($post["layaway_period_frequency"])?$post["layaway_period_frequency"]:$post["default_layaway_period_frequency"];
			    $productsArray['config_layaway_period_frequency'] = isset($post["config_layaway_period_frequency"])?$post["config_layaway_period_frequency"]:0;
			    
			    $this->_getWriteAdapter()->insert($this->getTable('layaway/products'), $productsArray);
			}else{
			    $this->_getWriteAdapter()->update($this->getTable('layaway/products'),
				    array(
					    'layaway_status' => isset($post["layaway_status"])?$post["layaway_status"]:$post["default_layaway_status"],
					    'layaway_fees' =>isset($post["layaway_fees"])?$post["layaway_fees"]:$post["default_layaway_fees"],
					    'layaway_first'=>isset($post["layaway_first"])?$post["layaway_first"]:$post["default_layaway_first"],
					    'layaway_maxinstallments'=>isset($post["layaway_maxinstallments"])?$post["layaway_maxinstallments"]:$post["default_layaway_maxinstallments"],
					    'config_layaway_status' =>isset($post["config_layaway_status"])?$post["config_layaway_status"]:0,
					    'config_layaway_fees' =>isset($post["config_layaway_fees"])?$post["config_layaway_fees"]:0,
					    'config_layaway_first'=>isset($post["config_layaway_first"])?$post["config_layaway_first"]:0,
					    'config_layaway_maxinstallments'=>isset($post["config_layaway_maxinstallments"])?$post["config_layaway_maxinstallments"]:0,
					    'layaway_period'=>isset($post["layaway_period"])?$post["layaway_period"]:$post["default_layaway_period"],
					    'config_layaway_period'=>isset($post["config_layaway_period"])?$post["config_layaway_period"]:0,
					    'layaway_period_frequency'=>isset($post["layaway_period_frequency"])?$post["layaway_period_frequency"]:$post["default_layaway_period_frequency"],
					    'config_layaway_period_frequency'=>isset($post["config_layaway_period_frequency"])?$post["config_layaway_period_frequency"]:0
					),
				    array("layaway_id =?" => $post["layaway_id"])
				);
			}
		      
			    
		    }
			
						
		}
		 catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
	    }
	}
	
	/**
	 * Shortcut to getRequest
	 */
	protected function _getRequest()
	{
		return Mage::app()->getRequest();
	}
}
