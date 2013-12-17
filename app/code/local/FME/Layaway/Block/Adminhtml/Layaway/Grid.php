<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\   FME Layaway extension  \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   FME                            ///////
 \\\\\\\                      * @package    FME_Layaway                    \\\\\\\
 ///////    * @author     Malik Tahir Mehmood <malik.tahir786@gmail.com>   ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\* @copyright  Copyright 2010 © free-magentoextensions.com All right reserved\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

class FME_Layaway_Block_Adminhtml_Layaway_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('layawayGrid');
    $this->setUseAjax(true);
    $this->setDefaultSort('order_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
  }

  /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass())->addFieldToFilter('is_layaway', 1);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order_id', array(
            'header'=> Mage::helper('sales')->__('Order Id'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'entity_id',
        ));
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('base_total_paid', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_total_paid',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'FME_Layaway_Block_Adminhtml_Layaway_Renderer_Totals',
        ));

        $this->addColumn('total_paid', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'total_paid',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'FME_Layaway_Block_Adminhtml_Layaway_Renderer_Totals',
        ));
        
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T.Paid (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T.Paid (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        $this->addColumn('base_layaway_remaining', array(
            'header' => Mage::helper('sales')->__(Mage::helper('layaway')->getLabelsStoredDatafor('remaining')),
            'index' => 'base_layaway_remaining',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('layaway_remaining', array(
            'header' => Mage::helper('sales')->__(Mage::helper('layaway')->getLabelsStoredDatafor('remaining')),
            'index' => 'layaway_remaining',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
    }
    protected function _prepareMassaction()
  {
    //$this->setMassactionIdField('order_id');
    //$this->getMassactionBlock()->setFormFieldName('layaway');
    return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/view', array('id' => $row->getId()));
  }
  public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}