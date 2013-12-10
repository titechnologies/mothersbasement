<?php
class Mage_Catalog_Block_Product_Bestseller extends Mage_Catalog_Block_Product_Abstract{
    public function __construct(){
        parent::__construct();
        $storeId = Mage::app()->getStore()->getId();
        $products = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc'); // most best sellers on top
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($products);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
        $products->setPageSize(8)->setCurPage(1);
        
        $this->setProductCollection($products);
    }
}
