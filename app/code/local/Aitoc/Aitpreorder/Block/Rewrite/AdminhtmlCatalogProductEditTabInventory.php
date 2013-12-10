<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/AdminhtmlCatalogProductEditTabInventory.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ ReUZTOYpjrwQEajM('cffa79206dcf4e24f826ee3f79ea8dee'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpreorder_Block_Rewrite_AdminhtmlCatalogProductEditTabInventory extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory
{	
	private $_restrictedTypes = array(
		Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
		Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
		Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
		Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
	);
	private $_currentAitProduct = null;	

	public function getAitCurrentProduct()
	{
		return $this->getProduct();		
	}

	public function getDisabled()
	{
		return $this->isPreorder() ? '' : 'disabled="disabled"';
	}

	public function getPreorderDescription()
	{
		$description = $this->getAitCurrentProduct()->getPreorderdescript();
		return strlen($description) ? $description : '';
	}

	public function isPreorder()
	{
		return (int) $this->getAitCurrentProduct()->getPreorder() == 1;
	}

	public function getDataArray()
	{
		return array(			
			'is_preorder' => $this->isPreorder(),
			'preorder_description' => $this->getPreorderDescription(),
			'disabled' => $this->getDisabled(),
		);
	}

	public function getRestrictedTypes()
	{
		return $this->_restrictedTypes;
	}

	public function canShowBlock()
	{
		return !in_array($this->getAitCurrentProduct()->getData('type_id'), $this->getRestrictedTypes());
	}

    protected function _toHtml()
    {
		$result = parent::_toHtml();

		if ($this->canShowBlock())
		{
			$preorder=$this->getLayout()->createBlock('core/template', '', $this->getDataArray())->setTemplate('aitpreorder/preorderinventory.phtml')->toHtml();
			$result = str_replace(__('Backorders') . '</label>', __('Backorders') . '\\' . __('Pre-Orders')  . '</label>', $result);
			$result = str_replace('</table>', $preorder, $result);
		}

		return $result;
    }     
    
} } 