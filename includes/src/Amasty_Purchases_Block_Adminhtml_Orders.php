<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Purchases_Block_Adminhtml_Orders extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ampurchasesGrid');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');
        
        $items = Mage::getResourceModel('sales/order_item_collection')
            ->addFieldToFilter('product_id', $id);
            
       $select = $items->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::ORDER)
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET)
            ->from('', array('order_id')); 
        
        $cond = str_replace('main_table', 'order_items', $select->__toString());    
        $cond = new Zend_Db_Expr($cond);
        
        
        $orders = array();
        
        if (version_compare(Mage::getVersion(), '1.4.1.0') < 0){
            $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->addExpressionAttributeToSelect('billing_name',
                    'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
                    array('billing_firstname', 'billing_lastname'))
                ->addExpressionAttributeToSelect('shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname', 'shipping_lastname'))
            ;             
        }
        else {
            $orders = Mage::getResourceModel('sales/order_grid_collection') 
                ->addFilterToMap('telephone', 'ba.telephone')
                ->addFilterToMap('email', 'ba.email')
            ;
            $select = $orders->getSelect();
            $select->joinLeft(
                array('ba' => $orders->getTable('sales/order_address')),
                "(main_table.entity_id = ba.parent_id AND ba.address_type = 'billing')",
                array('ba.telephone','ba.email')
            );            
        }
        
        $orders->addFieldToFilter('entity_id', array('in' => $cond)); 
        $this->setCollection($orders);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('customer')->__('Order #'),
            'width'     => '100',
            'index'     => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('customer')->__('Purchase On'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('billing_name', array(
            'header'    => Mage::helper('customer')->__('Bill to Name'),
            'index'     => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header'    => Mage::helper('customer')->__('Shipped to Name'),
            'index'     => 'shipping_name',
        ));

        if (version_compare(Mage::getVersion(), '1.4.1.0') >= 0){   
            $this->addColumn('email', array(
                'header'    => Mage::helper('customer')->__('Email'),
                'index'     => 'email',
            ));
            
            $this->addColumn('telephone', array(
                'header'    => Mage::helper('customer')->__('Phone'),
                'index'     => 'telephone',
            ));            
        }     


        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('customer')->__('Order Total'),
            'index'     => 'grand_total',
            'type'      => 'currency',
            'currency'  => 'order_currency_code',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('customer')->__('Bought From'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view' => true
            ));
        }
        $this->addColumn('action', array(
            'header'    => Mage::helper('customer')->__('Action'),
            'index'     => 'entity_id',
            'type'      => 'action',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption' => Mage::helper('sales')->__('View Order'),
                    'url'     => array('base'=>'adminhtml/sales_order/view'),
                    'field'   => 'order_id' 
                ),
            ),
            'is_system' => true,
        ));         
             
        $this->addExportType('*/*/exportOrdersCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportOrdersXml', Mage::helper('customer')->__('XML'));
                
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orders', array('_current' => true));
    }

    public function getRowUrl($row)
    {
       return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getEntityId()));
    }

}
