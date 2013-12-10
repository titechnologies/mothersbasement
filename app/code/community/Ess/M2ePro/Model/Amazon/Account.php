<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Account extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const OTHER_LISTINGS_SYNCHRONIZATION_NO  = 0;
    const OTHER_LISTINGS_SYNCHRONIZATION_YES = 1;

    const OTHER_LISTINGS_MAPPING_MODE_NO  = 0;
    const OTHER_LISTINGS_MAPPING_MODE_YES = 1;

    const OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 2;
    const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID       = 3;

    const OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE             = 0;
    const OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT          = 1;
    const OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE = 2;

    const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY        = 1;
    const OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY      = 2;
    const OTHER_LISTINGS_MAPPING_GENERAL_ID_DEFAULT_PRIORITY = 3;

    const OTHER_LISTINGS_MOVE_TO_LISTINGS_DISABLED = 0;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED  = 1;

    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE = 0;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_ALL  = 1;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_PRICE  = 2;
    const OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_QTY  = 3;

    const ORDERS_MODE_NO  = 0;
    const ORDERS_MODE_YES = 1;

    const MAGENTO_ORDERS_LISTINGS_MODE_NO  = 0;
    const MAGENTO_ORDERS_LISTINGS_MODE_YES = 1;

    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO  = 0;
    const MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES = 1;

    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    const MAGENTO_ORDERS_TAX_MODE_NONE    = 0;
    const MAGENTO_ORDERS_TAX_MODE_CHANNEL = 1;
    const MAGENTO_ORDERS_TAX_MODE_MAGENTO = 2;
    const MAGENTO_ORDERS_TAX_MODE_MIXED   = 3;

    const MAGENTO_ORDERS_CUSTOMER_MODE_GUEST      = 0;
    const MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED = 1;
    const MAGENTO_ORDERS_CUSTOMER_MODE_NEW        = 2;

    const MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO  = 0;
    const MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_YES = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    const MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM  = 1;

    const MAGENTO_ORDERS_STATUS_MAPPING_NEW        = 'pending';
    const MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING = 'processing';
    const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED    = 'complete';

    const MAGENTO_ORDERS_FBA_MODE_NO  = 0;
    const MAGENTO_ORDERS_FBA_MODE_YES = 1;

    const MAGENTO_ORDERS_FBA_STOCK_MODE_NO  = 0;
    const MAGENTO_ORDERS_FBA_STOCK_MODE_YES = 1;

    const MAGENTO_ORDERS_INVOICE_MODE_NO  = 0;
    const MAGENTO_ORDERS_INVOICE_MODE_YES = 1;

    const MAGENTO_ORDERS_SHIPMENT_MODE_NO  = 0;
    const MAGENTO_ORDERS_SHIPMENT_MODE_YES = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Account');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $items = $this->getRelatedSimpleItems('Amazon_Item','account_id',true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $this->delete();

        return true;
    }

    // ########################################

    public function getOtherListingsSynchronization()
    {
        return (int)$this->getData('other_listings_synchronization');
    }

    public function getOtherListingsMappingMode()
    {
        return (int)$this->getData('other_listings_mapping_mode');
    }

    public function getOtherListingsMappingSettings()
    {
        return $this->getSettings('other_listings_mapping_settings');
    }

    // ----------------------------------------

    public function getOtherListingsMappingGeneralIdMode()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('general_id', 'mode'),
                                     self::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE);

        return (int)$setting;
    }

    public function getOtherListingsMappingGeneralIdPriority()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('general_id', 'priority'),
                                     self::OTHER_LISTINGS_MAPPING_GENERAL_ID_DEFAULT_PRIORITY);

        return (int)$setting;
    }

    public function getOtherListingsMappingGeneralIdAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', array('general_id', 'attribute'));

        return $setting;
    }

    // ----------------------------------------

    public function getOtherListingsMappingSkuMode()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('sku', 'mode'),
                                     self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE);

        return (int)$setting;
    }

    public function getOtherListingsMappingSkuPriority()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('sku', 'priority'),
                                     self::OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY);

        return (int)$setting;
    }

    public function getOtherListingsMappingSkuAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('sku', 'attribute'));

        return $setting;
    }

    // ----------------------------------------

    public function getOtherListingsMappingTitleMode()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('title', 'mode'),
                                     self::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE);

        return (int)$setting;
    }

    public function getOtherListingsMappingTitlePriority()
    {
        $setting = $this->getSetting('other_listings_mapping_settings',
                                     array('title', 'priority'),
                                     self::OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY);

        return (int)$setting;
    }

    public function getOtherListingsMappingTitleAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', array('title', 'attribute'));

        return $setting;
    }

    // ########################################

    public function isOtherListingsSynchronizationEnabled()
    {
        return $this->getOtherListingsSynchronization() == self::OTHER_LISTINGS_SYNCHRONIZATION_YES;
    }

    public function isOtherListingsMappingEnabled()
    {
        return $this->getOtherListingsMappingMode() == self::OTHER_LISTINGS_MAPPING_MODE_YES;
    }

    // ----------------------------------------

    public function isOtherListingsMappingGeneralIdModeNone()
    {
        return $this->getOtherListingsMappingGeneralIdMode() == self::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE;
    }

    public function isOtherListingsMappingGeneralIdModeCustomAttribute()
    {
        return $this->getOtherListingsMappingGeneralIdMode() ==
            self::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    // ----------------------------------------

    public function isOtherListingsMappingSkuModeNone()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE;
    }

    public function isOtherListingsMappingSkuModeDefault()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT;
    }

    public function isOtherListingsMappingSkuModeCustomAttribute()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function isOtherListingsMappingSkuModeProductId()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID;
    }

    // ----------------------------------------

    public function isOtherListingsMappingTitleModeNone()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE;
    }

    public function isOtherListingsMappingTitleModeDefault()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT;
    }

    public function isOtherListingsMappingTitleModeCustomAttribute()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE;
    }

    // ########################################

    public function isOtherListingsMoveToListingsEnabled()
    {
        return (int)$this->getData('other_listings_move_mode') == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED;
    }

    //-----------------------------------------

    public function isOtherListingsMoveToListingsSynchModeNone()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE;
    }

    public function isOtherListingsMoveToListingsSynchModeAll()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_ALL;
    }

    public function isOtherListingsMoveToListingsSynchModeQty()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_QTY;
    }

    public function isOtherListingsMoveToListingsSynchModePrice()
    {
        $setting = $this->getSetting(
            'other_listings_move_settings', 'synch', self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_NONE
        );
        return $setting == self::OTHER_LISTINGS_MOVE_TO_LISTINGS_SYNCH_MODE_PRICE;
    }

    // ########################################

    public function getMarketplacesItems()
    {
        $marketplacesResults = array();

        $marketplacesCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace');

        foreach ($marketplacesCollection->getItems() as $marketplaceObj) {

            /** @var $marketplaceObj Ess_M2ePro_Model_Marketplace */

            $tempMarketplaceData = $this->getMarketplaceItem($marketplaceObj->getId());

            if (is_null($tempMarketplaceData)) {
                continue;
            }

            $tempMarketplaceData['object'] = $marketplaceObj;

            $marketplacesResults[] = $tempMarketplaceData;
        }

        return $marketplacesResults;
    }

    public function isExistMarketplaceItem($marketplaceId)
    {
        return !is_null($this->getMarketplaceIdData($marketplaceId));
    }

    public function getMarketplaceItem($marketplaceId)
    {
        return $this->getMarketplaceIdData($marketplaceId);
    }

    //-----------------------------------------

    public function addMarketplaceItem($marketplaceId,
                                       $serverHash,
                                       $accountMerchantId,
                                       $relatedStoreId = 0,
                                       array $info = array())
    {
        $newData = $this->createMarketplaceData($serverHash, $accountMerchantId, $relatedStoreId, $info);
        $this->setMarketplaceIdData($marketplaceId,$newData);
        $this->save();
    }

    public function updateMarketplaceItem($marketplaceId,
                                          $relatedStoreId = 0,
                                          array $info = array())
    {
        $data = $this->getMarketplaceIdData($marketplaceId);

        if (is_null($data)) {
            return;
        }

        $newData = $this->createMarketplaceData($data['server_hash'], $data['merchant_id'], $relatedStoreId, $info);

        $this->setMarketplaceIdData($marketplaceId,$newData);
        $this->save();
    }

    public function deleteMarketplaceItem($marketplaceId)
    {
        $this->setMarketplaceIdData($marketplaceId,NULL);
        $this->save();
    }

    //-----------------------------------------

    public function getRelatedStoreId($marketplaceId = null)
    {
        if (is_null($marketplaceId)) {
            $marketplacesData = $this->getMarketplacesData();
            if (!is_array($marketplacesData)) {
                return Mage_Core_Model_App::ADMIN_STORE_ID;
            }
            $marketplaceId = array_shift($marketplacesData);
        }

        $marketplaceItem = $this->getMarketplaceItem($marketplaceId);

        return !is_null($marketplaceItem)
            ? (int)$marketplaceItem['related_store_id']
            : Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    //-----------------------------------------

    private function getMarketplaceIdData($marketplaceId)
    {
        $marketplaceId = (int)$marketplaceId;

        $data = $this->getMarketplacesData();

        if (is_null($data)) {
            return NULL;
        }

        if (!isset($data[$marketplaceId])) {
            return NULL;
        }

        return $data[$marketplaceId];
    }

    private function setMarketplaceIdData($marketplaceId, $data = NULL)
    {
        $marketplaceId = (int)$marketplaceId;

        $allData = $this->getMarketplacesData();
        is_null($allData) && $allData = array();

        if (is_null($data)) {
            unset($allData[$marketplaceId]);
        } else {
            $allData[$marketplaceId] = $data;
        }

        $this->setMarketplacesData($allData);

        return true;
    }

    private function createMarketplaceData($serverHash, $accountMerchantId, $relatedStoreId = 0, array $info = array())
    {
        return array(
            'server_hash' => (string)$serverHash,
            'merchant_id' => (string)$accountMerchantId,
            'related_store_id' => (int)$relatedStoreId,
            'info' => $info
        );
    }

    //-----------------------------------------

    private function getMarketplacesData()
    {
        $data = $this->getData('marketplaces_data');

        if (is_null($data) || $data == '') {
            return NULL;
        }

        return json_decode($data,true);
    }

    private function setMarketplacesData($data = NULL)
    {
        if (!is_array($data) || count($data) <= 0) {
            $data = NULL;
        } else {
            $data = json_encode($data);
        }

        $this->setData('marketplaces_data',$data);

        return true;
    }

    // ########################################

    public function getOrdersMode()
    {
        return (int)$this->getData('orders_mode');
    }

    public function isOrdersModeEnabled()
    {
        return $this->getOrdersMode() == self::ORDERS_MODE_YES;
    }

    // ########################################

    public function isMagentoOrdersListingsModeEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_MODE_YES);

        return $setting == self::MAGENTO_ORDERS_LISTINGS_MODE_YES;
    }

    public function isMagentoOrdersListingsStoreCustom()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'store_mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT);

        return $setting == self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM;
    }

    public function getMagentoOrdersListingsStoreId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing', 'store_id'), 0);

        return (int)$setting;
    }

    //-----------------------------------------

    public function isMagentoOrdersListingsOtherModeEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES);

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES;
    }

    public function getMagentoOrdersListingsOtherStoreId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'store_id'), 0);

        return (int)$setting;
    }

    public function isMagentoOrdersListingsOtherProductImportEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'product_mode'),
                                     self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT);

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT;
    }

    public function getMagentoOrdersListingsOtherProductTaxClassId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('listing_other', 'product_tax_class_id'));

        return (int)$setting;
    }

    //-----------------------------------------

    public function getQtyReservationDays()
    {
        $setting = $this->getSetting('magento_orders_settings', array('qty_reservation', 'days'));

        return (int)$setting;
    }

    //-----------------------------------------

    public function isMagentoOrdersTaxModeNone()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_NONE;
    }

    public function isMagentoOrdersTaxModeChannel()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_CHANNEL;
    }

    public function isMagentoOrdersTaxModeMagento()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MAGENTO;
    }

    public function isMagentoOrdersTaxModeMixed()
    {
        $setting = $this->getSetting('magento_orders_settings', array('tax', 'mode'));

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MIXED;
    }

    //-----------------------------------------

    public function isMagentoOrdersCustomerGuest()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST;
    }

    public function isMagentoOrdersCustomerPredefined()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED;
    }

    public function isMagentoOrdersCustomerNew()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_NEW;
    }

    public function getMagentoOrdersCustomerId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'id'));

        return (int)$setting;
    }

    public function isMagentoOrdersCustomerNewSubscribed()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'subscription_mode'),
                                     self::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO);

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_YES;
    }

    public function isMagentoOrdersCustomerNewNotifyWhenCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'notifications', 'customer_created'));

        return (bool)$setting;
    }

    public function isMagentoOrdersCustomerNewNotifyWhenOrderCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'notifications', 'order_created'));

        return (bool)$setting;
    }

    public function isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'notifications', 'invoice_created'));

        return (bool)$setting;
    }

    public function getMagentoOrdersCustomerNewWebsiteId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'website_id'));

        return (int)$setting;
    }

    public function getMagentoOrdersCustomerNewGroupId()
    {
        $setting = $this->getSetting('magento_orders_settings', array('customer', 'group_id'));

        return (int)$setting;
    }

    //-----------------------------------------

    public function isMagentoOrdersStatusMappingDefault()
    {
        $setting = $this->getSetting('magento_orders_settings', array('status_mapping', 'mode'),
                                     self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT);

        return $setting == self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    public function getMagentoOrdersStatusProcessing()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'processing'));
    }

    public function getMagentoOrdersStatusShipped()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED;
        }

        return $this->getSetting('magento_orders_settings', array('status_mapping', 'shipped'));
    }

    //-----------------------------------------

    public function isMagentoOrdersInvoiceEnabled()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return true;
        }

        return $this->getSetting('magento_orders_settings', 'invoice_mode') == self::MAGENTO_ORDERS_INVOICE_MODE_YES;
    }

    public function isMagentoOrdersShipmentEnabled()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return true;
        }

        return $this->getSetting('magento_orders_settings', 'shipment_mode') == self::MAGENTO_ORDERS_SHIPMENT_MODE_YES;
    }

    //-----------------------------------------

    public function isMagentoOrdersFbaModeEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('fba', 'mode'),
                                     self::MAGENTO_ORDERS_FBA_MODE_YES);

        return $setting == self::MAGENTO_ORDERS_FBA_MODE_YES;
    }

    public function isMagentoOrdersFbaStockEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', array('fba', 'stock_mode'));

        return $setting == self::MAGENTO_ORDERS_FBA_STOCK_MODE_YES;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('account');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('account');
        return parent::delete();
    }

    // ########################################
}