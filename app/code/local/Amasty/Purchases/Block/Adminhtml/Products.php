<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Purchases_Block_Adminhtml_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ampurchasesGrid');
        $this->setUseAjax(true);
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');
        $orders = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToFilter('customer_id', $id);
                    
        $orderIds = array();
        foreach ($orders as $order){
            $orderIds[] = $order->getId();
        }
        
        $items = Mage::getResourceModel('sales/order_item_collection')
            ->filterByParent()
//            ->addFilterToMap('value', 't.value')
            ->addFieldToFilter('order_id', $orderIds);
            
//        $select = $items->getSelect();   
//        $select->joinLeft(
//            array('t' => Mage::getModel('catalog/product')->getResource()->getTable('catalog_product_entity_datetime')),
//            "(main_table.product_id = t.entity_id AND t.store_id = 0 AND attribute_id=66)",
//            array('t.value')
//        );            
            
        $this->setCollection($items);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('ampurchases');

       
        $this->addColumn('created_at', array(
            'header'    => $hlp->__('Purchase On'),
            'index'     => 'created_at',
            'gmtoffset' => true,
            'type'      => 'date',
        ));        

        $this->addColumn('sku', array(
            'header'    => $hlp->__('SKU'),
            'index'     => 'sku',
        ));
        
        $this->addColumn('name', array(
            'header'    => $hlp->__('Name'),
            'index'     => 'name',
        ));
        
//        $this->addColumn('value', array(
//            'header'    => $hlp->__('Date'),
//            'index'     => 'value',
//            'gmtoffset' => true,
//            'type'      => 'date',            
//        ));
        
        $this->addColumn('qty_ordered', array(
            'header'    => $hlp->__('Qty'),
            'index'     => 'qty_ordered',
            'getter'    => array($this, 'getFormattedQty'),
        ));
        
        $this->addColumn('price', array(
            'header'    => $hlp->__('Price'),
            'index'     => 'price',
            'getter'    => array($this, 'getFormattedPrice'),
        ));        
        
        $this->addColumn('action', array(
            'header'    => Mage::helper('customer')->__('Action'),
            'index'     => 'order_id',
            'type'      => 'action',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption' => $hlp->__('View Order'),
                    'url'     => array('base'=>'adminhtml/sales_order/view'),
                    'field'   => 'order_id' 
                ),
            ),
            'is_system' => true,
        ));    
             
        $this->addExportType('*/*/exportProductsCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportProductsXml', Mage::helper('customer')->__('XML'));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', array('_current' => true));
    }

    public function getRowUrl($row)
    {
       return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));
    }
    
    public function getFormattedPrice($row)
    {
       return '$' . round($row->getPrice(),2);
    }
    
    public function getFormattedQty($row)
    {
       return intVal($row->getQtyOrdered());
    }

}
