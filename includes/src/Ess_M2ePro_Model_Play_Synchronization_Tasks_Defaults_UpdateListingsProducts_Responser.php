<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_UpdateListingsProducts_Responser
{
    protected $params = array();
    protected $synchronizationLog = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    protected $logActionId = NULL;

    // ########################################

    public function initialize(array $params = array(),
                               Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->params = $params;
        $this->marketplace = $marketplace;
        $this->account = $account;
    }

    // ########################################

    public function unsetLocks($hash, $fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'].'_'.$this->params['marketplace_id'];

        $lockItem->setNick($tempNick);
        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL,$hash);
        $this->getAccount()->deleteObjectLocks('synchronization',$hash);
        $this->getAccount()->deleteObjectLocks('synchronization_play',$hash);
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,$hash
        );

        $this->getMarketplace()->deleteObjectLocks(NULL,$hash);
        $this->getMarketplace()->deleteObjectLocks('synchronization',$hash);
        $this->getMarketplace()->deleteObjectLocks('synchronization_play',$hash);
        $this->getMarketplace()->deleteObjectLocks(
            Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,$hash
        );

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_play_processed_inventory');
        $connWrite->delete($tempTable,array('`hash` = ?'=>(string)$hash));

        $fail && $this->getSynchLogModel()->addMessage(Mage::helper('M2ePro')->__($message),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    public function processSucceededResponseData($receivedItems, $hash, $nextPart)
    {
        //----------------------
        $tempItems = array();
        foreach ($receivedItems as $receivedItem) {
            if (empty($receivedItem['sku'])) {
                continue;
            }
            $tempItems[$receivedItem['sku']] = $receivedItem;
        }
        $receivedItems = $tempItems;
        unset($tempItems);
        //----------------------

        try {

            $this->updateReceivedListingsProducts($receivedItems);
            $this->updateNotReceivedListingsProducts($receivedItems,$hash,$nextPart);

            is_null($nextPart) && $this->resetIgnoreNextInventorySynch();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);

            $this->getSynchLogModel()->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    // ########################################

    protected function updateReceivedListingsProducts($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            if ((int)$existingItem['ignore_next_inventory_synch'] ==
                Ess_M2ePro_Model_Play_Listing_Product::IGNORE_NEXT_INVENTORY_SYNCH_YES) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'play_listing_id' => (int)$receivedItem['play_listing_id'],
                'dispatch_to' => (string)$receivedItem['dispatch_to'],
                'dispatch_from' => (string)$receivedItem['dispatch_from'],
                'online_price_gbr' => (float)$receivedItem['price_gbr'],
                'online_price_euro' => (float)$receivedItem['price_euro'],
                'online_qty' => (int)$receivedItem['qty'],
                'condition' => (string)$receivedItem['condition'],
                'condition_note' => (string)$receivedItem['condition_note'],
                'start_date' => (string)date('c',strtotime($receivedItem['start_date']))
            );

            if ($newData['online_qty'] > 0) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            } else {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }

            if (is_null($existingItem['general_id']) || is_null($existingItem['general_id_type'])) {
                $newData['general_id'] = (string)$receivedItem['general_id'];
                $newData['general_id_type'] = (string)$receivedItem['general_id_type'];
            }

            $existingData = array(
                'play_listing_id' => (int)$receivedItem['play_listing_id'],
                'dispatch_to' => (string)$existingItem['dispatch_to'],
                'dispatch_from' => (string)$existingItem['dispatch_from'],
                'online_price_gbr' => (float)$existingItem['online_price_gbr'],
                'online_price_euro' => (float)$existingItem['online_price_euro'],
                'online_qty' => (int)$existingItem['online_qty'],
                'condition' => (string)$existingItem['condition'],
                'condition_note' => (string)$existingItem['condition_note'],
                'start_date' => (string)date('c',strtotime($existingItem['start_date'])),
                'status' => (int)$existingItem['status']
            );

            if (is_null($existingItem['general_id']) || is_null($existingItem['general_id_type'])) {
                $existingData['general_id'] = NULL;
                $existingData['general_id_type'] = NULL;
            }

            if ($newData == $existingData) {
                continue;
            }

            if ($newData['online_qty'] > 0) {
                $newData['end_date'] = NULL;
            } else {
                $newData['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
            }

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            if ($newData['status'] != $existingItem['status']) {

                Mage::getModel('M2ePro/ProductChange')
                    ->updateAttribute(
                        $existingItem['product_id'], 'play_listing_product_status',
                        'listing_product_id_'.$existingItem['listing_product_id'].'_status_'.$existingItem['status'],
                        'listing_product_id_'.$existingItem['listing_product_id'].'_status_'.$newData['status'],
                        Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION );

                $tempLogMessage = '';
                switch ($newData['status']) {
                    case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                        // Parser hack ->__('Item status was successfully changed to "Active".');
                        $tempLogMessage = 'Item status was successfully changed to "Active".';
                        break;
                    case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                        // Parser hack ->__('Item status was successfully changed to "Inactive".');
                        $tempLogMessage = 'Item status was successfully changed to "Inactive".';
                        break;
                }

                $tempLog->addProductMessage(
                    $existingItem['listing_id'],
                    $existingItem['product_id'],
                    $existingItem['listing_product_id'],
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $listingProductObj = Mage::helper('M2ePro/Component_Play')
                                    ->getObject('Listing_Product',(int)$existingItem['listing_product_id']);

            $newData['condition_note'] == '' && $newData['condition_note'] = new Zend_Db_Expr("''");

            $listingProductObj->addData($newData)->save();
        }
    }

    protected function updateNotReceivedListingsProducts($receivedItems,$hash,$nextPart)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_play_processed_inventory');

        //--------------------------
        $chunckedArray = array_chunk($receivedItems,1000);

        foreach ($chunckedArray as $partReceivedItems) {

            $inserts = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $inserts[] = array(
                    'sku' => $partReceivedItem['sku'],
                    'hash' => $hash
                );
            }

            $connWrite->insertMultiple($tempTable, $inserts);
        }
        //--------------------------

        if (!is_null($nextPart)) {
            return;
        }

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $generalTemplateTable = Mage::getResourceModel('M2ePro/Template_General')->getMainTable();
        $listingProductMainTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->join(array('gt' => $generalTemplateTable), 'l.template_general_id = gt.id', array());
        $collection->getSelect()->joinLeft(array('api' => $tempTable),
                    '`second_table`.sku = `api`.sku AND `api`.`hash` = \''.$hash.'\'', array('sku'));
        $collection->getSelect()->where('gt.marketplace_id = ?',(int)$this->getMarketplace()->getId());
        $collection->getSelect()->where('gt.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('second_table.ignore_next_inventory_synch != ?',
                                        Ess_M2ePro_Model_Buy_Listing_Product::IGNORE_NEXT_INVENTORY_SYNCH_YES);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);
        $collection->getSelect()->having('`api`.sku IS NULL');

        $tempColumns = array('main_table.id','main_table.status','main_table.listing_id',
                             'main_table.product_id','api.sku');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

        $notReceivedIds = array();
        while ($notReceivedItem = $stmtTemp->fetch()) {

            if (!in_array((int)$notReceivedItem['id'],$notReceivedIds)) {
                $tempLog->addProductMessage(
                    $notReceivedItem['listing_id'],
                    $notReceivedItem['product_id'],
                    $notReceivedItem['id'],
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    // Parser hack ->__('Item status was successfully changed to "Inactive".');
                    'Item status was successfully changed to "Inactive".',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }
        $notReceivedIds = array_unique($notReceivedIds);

        $bind = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
        );

        $chunckedIds = array_chunk($notReceivedIds,1000);
        foreach ($chunckedIds as $partIds) {
            $where = '`id` IN ('.implode(',',$partIds).')';
            $connWrite->update($listingProductMainTable,$bind,$where);
        }
    }

    protected function resetIgnoreNextInventorySynch()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $generalTemplateTable = Mage::getResourceModel('M2ePro/Template_General')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $dbSelect = $connWrite->select();
        $dbSelect->from(array('lp' => $listingProductTable), array());
        $dbSelect->join(array('l' => $listingTable), 'lp.listing_id = l.id', array());
        $dbSelect->join(array('gt' => $generalTemplateTable), 'l.template_general_id = gt.id', array());
        $dbSelect->where('gt.marketplace_id = ?',(int)$this->getMarketplace()->getId());
        $dbSelect->where('gt.account_id = ?',(int)$this->getAccount()->getId());
        $dbSelect->where('lp.component_mode = ?',Ess_M2ePro_Helper_Component_Play::NICK);

        $dbSelect->reset(Zend_Db_Select::COLUMNS)->columns(array('lp.id'));

        $listingProductTable = Mage::getResourceModel('M2ePro/Play_Listing_Product')->getMainTable();

        $bind = array(
            'ignore_next_inventory_synch' => Ess_M2ePro_Model_Play_Listing_Product::IGNORE_NEXT_INVENTORY_SYNCH_NO
        );
        $where = new Zend_Db_Expr('`listing_product_id` IN ('.$dbSelect->__toString().')');

        $connWrite->update($listingProductTable,$bind,$where);
    }

    // ########################################

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $generalTemplateTable = Mage::getResourceModel('M2ePro/Template_General')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->join(array('gt' => $generalTemplateTable), 'l.template_general_id = gt.id', array());
        $collection->getSelect()->where('gt.marketplace_id = ?',(int)$this->getMarketplace()->getId());
        $collection->getSelect()->where('gt.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where("`second_table`.`sku` is not null and `second_table`.`sku` != ''");

        $dbSelect = $collection->getSelect();

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array('main_table.listing_id',
                                 'main_table.product_id','main_table.status',
                                 'second_table.sku','second_table.general_id', 'second_table.general_id_type',
                                 'second_table.play_listing_id',
                                 'second_table.online_price_gbr','second_table.online_price_euro',
                                 'second_table.online_qty','second_table.dispatch_to','second_table.dispatch_from',
                                 'second_table.condition','second_table.condition_note','second_table.start_date',
                                 'second_table.ignore_next_inventory_synch',
                                 'second_table.listing_product_id');
        }

        $dbSelect->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($dbSelect->__toString());

        return $stmtTemp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->marketplace;
    }

    //-----------------------------------------

    protected function getLogActionId()
    {
        if (!is_null($this->logActionId)) {
            return $this->logActionId;
        }

        return $this->logActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
    }

    protected function getSynchLogModel()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        /** @var $runs Ess_M2ePro_Model_Synchronization_Run */
        $runs = Mage::getModel('M2ePro/Synchronization_Run');
        $runs->start(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $runsId = $runs->getLastId();
        $runs->stop();

        /** @var $logs Ess_M2ePro_Model_Synchronization_Log */
        $logs = Mage::getModel('M2ePro/Synchronization_Log');
        $logs->setSynchronizationRun($runsId);
        $logs->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);
        $logs->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_DEFAULTS);

        $this->synchronizationLog = $logs;

        return $this->synchronizationLog;
    }

    // ########################################
}