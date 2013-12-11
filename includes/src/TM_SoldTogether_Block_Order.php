<?php

class TM_SoldTogether_Block_Order extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime'    => 86400,
            'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }

    public function getCacheKeyInfo()
    {
        $productId = 0;
        if ($product = Mage::registry('product')) {
            $productId = $product->getId();
        }
        return array(
            'TM_SOLD_TOGETHER_ORDER',
            Mage::app()->getStore()->getId(),
            Mage::app()->getStore()->getCurrentCurrencyCode(),
            Mage::getDesign()->getPackageName(),
            $this->getTemplate(),
            $this->getProductsCount(),
            $this->getNameInLayout(),
            $productId
        );
    }

    protected function _beforeToHtml()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(
            Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()
        );
        $product = Mage::registry('product');
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToFilter('entity_id', array(
                'in' => $product ? Mage::getResourceModel('soldtogether/order')->getProductIds($product->getId(), $this->getProductsCount()) : array(0)
            ))
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1);

        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    }

    public function setProductsCount($count)
    {
        $this->_productsCount = $count;
        return $this;
    }

    public function setColumnsCount($count)
    {
        $this->_columnsCount = $count;
        return $this;
    }

    public function getProductsCount()
    {
        return Mage::getStoreConfig('soldtogether/order/productscount');
    }

    public function getColumnsCount()
    {
        return Mage::getStoreConfig('soldtogether/order/columns');
    }
}
