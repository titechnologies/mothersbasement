<?php
class EW_Automatedproducts_Model_System_Config_Source_Conditions
{
	public function toOptionArray()
	{
		return array(
// 			array('value' => 'category', 'label'=>Mage::helper('adminhtml')->__('Same Category products')),
// 			array('value' => 'attribute', 'label'=>Mage::helper('adminhtml')->__('Same Attribute Set Products')),
			array('value' => 'lessprice', 'label'=>Mage::helper('adminhtml')->__('Price Less then current product')),
			array('value' => 'moreprice', 'label'=>Mage::helper('adminhtml')->__('Price More then current product')),
		);
	}
	public function toArray()
	{
		return array(
// 				'category' => Mage::helper('adminhtml')->__('Same Category products'),
// 				'attribute' => Mage::helper('adminhtml')->__('Same Attribute Set Products'),
				'lessprice' => Mage::helper('adminhtml')->__('Price Less then current product'),
				'moreprice' => Mage::helper('adminhtml')->__('Price More then current product'),
		);
	}
} 
?>