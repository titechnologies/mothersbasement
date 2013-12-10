<?php
class EW_Automatedproducts_Block_Catalog_Product_List_Crosssell extends Mage_Catalog_Block_Product_List_Crosssell
{
	protected function _prepareData()
	{
		$helper = Mage::helper('automatedproducts');
		$config = $helper->getConfig("automatedproducts/crosssellproducts");
		$conditions = $helper->getConditions("crosssellproducts");
		//if($helper->isEnabled('crosssellproducts')) {
			$store_id = Mage::app()->getStore()->getId();
			$product = Mage::registry('product');
			$product = Mage::getModel('catalog/product')->load($product->getId());
			$this->_itemCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect("*");
			$this->_itemCollection->setStoreId($store_id)->addStoreFilter($store_id);
 			$conditionArray = array();
			$conditionArray[] = array(
						'attribute' => 'brand',
						'eq'        => $product->getBrand(),
					);
			if($product->getLicense() != null){
				$conditionArray[] = array(
					'attribute' => 'license',
					'eq'        => $product->getLicense(),
				);
			}
			if($product->getSeries() != null){
				$conditionArray[] = array(
					'attribute' => 'series',
					'eq'        => $product->getSeries(),
				);
			}
			if($product->getType() != null){
				$conditionArray[] = array(
					'attribute' => 'type',
					'eq'        => $product->getType(),
				);
			}
			$this->_itemCollection->addAttributeToFilter($conditionArray);
 			$this->_itemCollection->addStoreFilter()
 									->setPageSize((int)$config['limit'])
 									->setCurPage(1)
 									->addIdFilter(array($product->getId()), true);
 			Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
 			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_itemCollection);
 			$this->_itemCollection->addAttributeToFilter('visibility', array('in' => array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) ));
 			$this->_itemCollection->getSelect()->order('rand()');
 			$this->_itemCollection->load();
			return $this;
		//}
		return parent::_prepareData();
	}
} 
?>