<?php
class EW_Automatedproducts_Model_System_Config_Source_Collectionmode
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'merge', 'label'=>Mage::helper('adminhtml')->__('be merge with selected products')),
			array('value' => 'override', 'label'=>Mage::helper('adminhtml')->__('be override selected products')),
			array('value' => 'nodisplay', 'label'=>Mage::helper('adminhtml')->__('not display if related products assigned to product')),
		);
	}
	public function toArray()
	{
		return array(
				'merge' => Mage::helper('adminhtml')->__('be merge to selected products'),
				'override' => Mage::helper('adminhtml')->__('be override to selected products'),
				'nodisplay' => Mage::helper('adminhtml')->__('not display if related products assigned to product'),
		);
	}
} 
?>