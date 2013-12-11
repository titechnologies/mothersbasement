<?php
class Ewall_Bestsellerslider_Block_Bestsellerslider extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBestsellerslider()     
     { 
        if (!$this->hasData('bestsellerslider')) {
            $this->setData('bestsellerslider', Mage::registry('bestsellerslider'));
        }
        return $this->getData('bestsellerslider');
        
    }
	public function getProducts()
    {
    	$storeId    = Mage::app()->getStore()->getId();
    	$products = Mage::getResourceModel('reports/product_collection')
    		->addOrderedQty()
            ->addAttributeToSelect('*')
			->addMinimalPrice()
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc');	
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
    public function getContentproducts()
    {
    	$storeId    = Mage::app()->getStore()->getId();
    	$products = Mage::getResourceModel('reports/product_collection')
    		->addOrderedQty()
            ->addAttributeToSelect('*')
			->addMinimalPrice()
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc');	
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getContentconfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('bestsellerslider');
		if (isset($config['bestsellerslider_config']) ) {
			$value = $config['bestsellerslider_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
	public function getContentconfig($att) 
	{
		$config = Mage::getStoreConfig('bestsellerslider');
		if (isset($config['bestsellerslider_content']) ) {
			$value = $config['bestsellerslider_content'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}
