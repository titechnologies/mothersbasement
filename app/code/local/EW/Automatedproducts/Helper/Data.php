<?php
class EW_Automatedproducts_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getConfig($path = "automatedproducts")
	{
		$store = Mage::app()->getStore()->getId();
		return Mage::getStoreConfig($path,$store);
	}
	public function isEnabled($scope)
	{
		$store = Mage::app()->getStore()->getId();
		return Mage::getStoreConfig("automatedproducts/".$scope."/enabled",$store);
	}
	public function getConditions($scope)
	{
		$store = Mage::app()->getStore()->getId();
		if(($conditions = Mage::getStoreConfig("automatedproducts/".$scope."/conditions",$store)) != null)
		{
			return explode(",",$conditions);
		}
		return array();
	}
}
?>