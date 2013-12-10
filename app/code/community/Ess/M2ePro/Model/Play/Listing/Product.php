<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Play_Listing_Product extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    const GENERAL_ID_SEARCH_STATUS_NONE  = 0;
    const GENERAL_ID_SEARCH_STATUS_SET_MANUAL  = 1;
    const GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC  = 2;
    const GENERAL_ID_SEARCH_STATUS_PROCESSING = 3;

    const TRIED_TO_LIST_YES = 1;
    const TRIED_TO_LIST_NO  = 0;

    const IGNORE_NEXT_INVENTORY_SYNCH_YES = 1;
    const IGNORE_NEXT_INVENTORY_SYNCH_NO  = 0;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Listing_Product');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getParentObject()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getParentObject()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getParentObject()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getParentObject()->getSynchronizationTemplate();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Play_Listing
     */
    public function getPlayListing()
    {
        return $this->getListing()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Play_Template_General
     */
    public function getPlayGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_SellingFormat
     */
    public function getPlaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Description
     */
    public function getPlayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Synchronization
     */
    public function getPlaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    // ########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    //-----------------------------------------

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    public function getGeneralIdType()
    {
        return $this->getData('general_id_type');
    }

    //-----------------------------------------

    public function getPlayListingId()
    {
        return (int)$this->getData('play_listing_id');
    }

    //-----------------------------------------

    public function getLinkInfo()
    {
        return $this->getData('link_info');
    }

    //-----------------------------------------

    public function getDispatchTo()
    {
        return $this->getData('dispatch_to');
    }

    public function getDispatchFrom()
    {
        return $this->getData('dispatch_from');
    }

    //-----------------------------------------

    public function getOnlinePriceGbr()
    {
        return (float)$this->getData('online_price_gbr');
    }

    public function getOnlinePriceEuro()
    {
        return (float)$this->getData('online_price_euro');
    }

    //-----------------------------------------

    public function getOnlineShippingPriceGbr()
    {
        return (float)$this->getData('online_shipping_price_gbr');
    }

    public function getOnlineShippingPriceEuro()
    {
        return (float)$this->getData('online_shipping_price_euro');
    }

    //-----------------------------------------

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function getCondition()
    {
        return $this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    //-----------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    //-----------------------------------------

    public function isIgnoreNextInventorySynch()
    {
        return $this->getData('ignore_next_inventory_synch') == self::IGNORE_NEXT_INVENTORY_SYNCH_YES;
    }

    public function isTriedToList()
    {
        return $this->getData('tried_to_list') == self::TRIED_TO_LIST_YES;
    }

    // ########################################

    public function getGeneralIdSearchStatus()
    {
        return (int)$this->getData('general_id_search_status');
    }

    public function isGeneralIdSearchStatusNone()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_NONE;
    }

    public function isGeneralIdSearchStatusSetManual()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_SET_MANUAL;
    }

    public function isGeneralIdSearchStatusSetAutomatic()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;
    }

    //-----------------------------------------

    public function getGeneralIdSearchSuggestData()
    {
        $temp = $this->getData('general_id_search_suggest_data');
        return is_null($temp) ? array() : json_decode($temp,true);
    }

    // ########################################

    public function getAddingSku()
    {
        $temp = $this->getData('cache_adding_sku');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayGeneralTemplate()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SKU_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SKU_MODE_PRODUCT_ID) {
            $result = $this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_sku',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingGeneralId()
    {
        $temp = $this->getData('cache_adding_general_id');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 0;
        $src = $this->getPlayGeneralTemplate()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        } else {
            $result = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $this->setData('cache_adding_general_id',$result);

        return $result;
    }

    public function getAddingGeneralIdType()
    {
        $temp = $this->getData('cache_adding_general_id_type');

        if (!empty($temp)) {
            return $temp;
        }

        $result = $this->getPlayGeneralTemplate()->getGeneralIdMode();
        $result == Ess_M2ePro_Model_Play_Template_General::GENERAL_ID_MODE_NOT_SET && $result = NULL;

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_general_id_type',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingDispatchTo()
    {
        $temp = $this->getData('cache_adding_dispatch_to');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayGeneralTemplate()->getDispatchToSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = $this->replaceDispatchToValue($result);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_dispatch_to',$result);

        return $result;
    }

    public function getAddingDispatchFrom()
    {
        $temp = $this->getData('cache_adding_dispatch_from');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayGeneralTemplate()->getDispatchFromSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::DISPATCH_FROM_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::DISPATCH_FROM_MODE_DEFAULT) {
            $result = $src['value'];
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_dispatch_from',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingCondition()
    {
        $temp = $this->getData('cache_adding_condition');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayGeneralTemplate()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::CONDITION_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = $this->replaceConditionValue($result);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_condition',$result);

        return $result;
    }

    public function getAddingConditionNote()
    {
        $temp = $this->getData('cache_adding_condition_note');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayGeneralTemplate()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::CONDITION_NOTE_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_condition_note',$result);

        return $result;
    }

    // ########################################

    public function getShippingPriceGbr()
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_EUROPA) {
            return $price;
        }

        $src = $this->getPlayGeneralTemplate()->getShippingPriceGbrSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SHIPPING_PRICE_GBR_MODE_NONE) {
            return $price;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE) {
            $price = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE) {
            $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $price < 0 && $price = 0;

        return round($price,2);
    }

    public function getShippingPriceEuro()
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_UK) {
            return $price;
        }

        $src = $this->getPlayGeneralTemplate()->getShippingPriceEuroSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SHIPPING_PRICE_EURO_MODE_NONE) {
            return $price;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE) {
            $price = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_General::SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE) {
            $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $price < 0 && $price = 0;

        return round($price,2);
    }

    // ########################################

    public function getPriceGbr($includeShippingPrice = true)
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_EUROPA) {
            return $price;
        }

        $src = $this->getPlaySellingFormatTemplate()->getPriceGbrSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);
        $price = $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);

        if ($includeShippingPrice) {
            $price += $this->getShippingPriceGbr();
        }

        return round($price,2);
    }

    public function getPriceEuro($includeShippingPrice = true)
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_UK) {
            return $price;
        }

        $src = $this->getPlaySellingFormatTemplate()->getPriceEuroSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);
        $price = $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);

        if ($includeShippingPrice) {
            $price += $this->getShippingPriceEuro();
        }

        return round($price,2);
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '')
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                }
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_FINAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice);
                } else {
                    $customerGroupId = $this->getPlaySellingFormatTemplate()->getCustomerGroupId();
                    $price = $this->getMagentoProduct()->getFinalPrice($customerGroupId);
                }
                break;

            default:
            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $productPrice = Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_PRODUCT;
                    $price = $this->getBaseGroupedProductPrice($productPrice);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                }
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType)
    {
        $price = 0;

        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;

            /** @var $tempProduct Ess_M2ePro_Model_Magento_Product */
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL:
                    $tempPrice = $tempProduct->getSpecialPrice();
                    $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_FINAL:
                    $tempProduct = Mage::getModel('M2ePro/Magento_Product')
                                            ->setProductId($tempProduct->getProductId())
                                            ->setStoreId($this->getListing()->getStoreId());
                    $customerGroupId = $this->getPlaySellingFormatTemplate()->getCustomerGroupId();
                    $tempPrice = $tempProduct->getFinalPrice($customerGroupId);
                    break;
            }

            $tempPrice = (float)$tempPrice;

            if ($tempPrice < $price || $price == 0) {
                $price = $tempPrice;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getQty($productMode = false)
    {
        $qty = 0;
        $src = $this->getPlaySellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_SINGLE:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = 1;
                }
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_NUMBER:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = $src['value'];
                }
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = $this->_getProductGeneralQty();
                break;
        }

        //-- Check max posted QTY on channel
        if ($src['qty_max_posted_value'] > 0 && $qty > $src['qty_max_posted_value']) {
            $qty = $src['qty_max_posted_value'];
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    //-----------------------------------------

    protected function _getProductGeneralQty()
    {
        if ($this->getMagentoProduct()->isStrictVariationProduct()) {
            return $this->getParentObject()->_getOnlyVariationProductQty();
        }
        return (int)floor($this->getMagentoProduct()->getQty());
    }

    //-----------------------------------------

    protected function replaceConditionValue($value)
    {
        $value = (int)$value;
        $replacementCondition = array(
            1 => Ess_M2ePro_Model_Play_Template_General::CONDITION_NEW,
            2 => Ess_M2ePro_Model_Play_Template_General::CONDITION_USED_LIKE_NEW,
            3 => Ess_M2ePro_Model_Play_Template_General::CONDITION_USED_VERY_GOOD,
            4 => Ess_M2ePro_Model_Play_Template_General::CONDITION_USED_GOOD,
            5 => Ess_M2ePro_Model_Play_Template_General::CONDITION_USED_AVERAGE,
            6 => Ess_M2ePro_Model_Play_Template_General::CONDITION_COLLECTABLE_LIKE_NEW,
            7 => Ess_M2ePro_Model_Play_Template_General::CONDITION_COLLECTABLE_VERY_GOOD,
            8 => Ess_M2ePro_Model_Play_Template_General::CONDITION_COLLECTABLE_GOOD,
            9 => Ess_M2ePro_Model_Play_Template_General::CONDITION_COLLECTABLE_AVERAGE,
            10 => Ess_M2ePro_Model_Play_Template_General::CONDITION_REFURBISHED
        );
        return array_key_exists($value,$replacementCondition) ? $replacementCondition[$value] : $value;
    }

    protected function replaceDispatchToValue($value)
    {
        $value = strtolower(trim($value));
        $replacementDispatchTo = array(
            'uk' => Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_UK,
            'europe' => Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_EUROPA,
            'europe_uk' => Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_BOTH
        );
        return array_key_exists($value,$replacementDispatchTo) ? $replacementDispatchTo[$value] : $value;
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Play_Connector_Product_Dispatcher::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Play_Connector_Product_Dispatcher::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Play_Connector_Product_Dispatcher::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Play_Connector_Product_Dispatcher::ACTION_STOP, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Play_Connector')->getProductDispatcher();
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    // ########################################
}