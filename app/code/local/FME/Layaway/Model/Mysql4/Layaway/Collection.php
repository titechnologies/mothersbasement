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

class FME_Layaway_Model_Mysql4_Layaway_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('layaway/layaway');
    }
	
	/**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Mage_Cms_Model_Mysql4_News_News_Collection
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()->join(
            array('store_table' => $this->getTable('layaway_store')),
            'main_table.layaway_id = store_table.layaway_id',
            array()
        )
        ->where('store_table.store_id in (?)', array(0, $store));

        return $this;
    }
	
	public function prepareSummary()
	{
			$layawayTable = Mage::getSingleton('core/resource')->getTableName('layaway');
			$this->setConnection($this->getResource()->getReadConnection());
			$this->getSelect()
				->from(array('main_table'=>$layawayTable),'*')
				->where('status = ?', 1)
				->order('created_time','asc');;
			return $this;
	}
	
	public function getLayaway()
	{
			$layawayTable = Mage::getSingleton('core/resource')->getTableName('layaway');
			$this->setConnection($this->getResource()->getReadConnection());
			$this->getSelect()
				->from(array('main_table'=>$layawayTable),'*')
				->where('status = ?', 1)
				->order('created_time','asc');
			return $this;
	}
	
	public function getDetail($layaway_id)
	{
		$layawayTable = Mage::getSingleton('core/resource')->getTableName('layaway');
		$this->setConnection($this->getResource()->getReadConnection());
		$this->getSelect()
			->from(array('main_table'=>$layawayTable),'*')
			->where('layaway_id = ?', $layaway_id);
		return $this;
	}
	
	public function getLayawayBlock()
	{
			$layawayTable = Mage::getSingleton('core/resource')->getTableName('layaway');
			$this->setConnection($this->getResource()->getReadConnection());
			$this->getSelect()
				->from(array('main_table'=>$layawayTable),'*')
				->where('status = ?', 1)
				->order('created_time DESC')
				->limit(5);
			return $this;
	}
	
    public function toOptionArray()
    {
        return $this->_toOptionArray('layaway_id', 'title');
    }
	
    public function addLayawayFilter($layaway)
    {
        if (is_array($layaway)) {
            $condition = $this->getConnection()->quoteInto('main_table.layaway_id IN(?)', $layaway);
        }
        else {
            $condition = $this->getConnection()->quoteInto('main_table.layaway_id=?', $layaway);
        }
        return $this->addFilter('layaway_id', $condition, 'string');
    }
}