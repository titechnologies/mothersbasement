<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Play_Product_List_Multiple
    extends Ess_M2ePro_Model_Connector_Server_Play_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','update','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'list';
    }

    protected function getResponserModel()
    {
        return 'Play_Product_List_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function prepareListingsProducts($listingsProducts)
    {
        $tempListingsProducts = array();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isNotListed()) {

                // Parser hack -> Mage::helper('M2ePro')->__('Item is already on Play.com, or not available.');
                $this->addListingsProductsLogsMessage($listingProduct,
                                                      'Item is already on Play.com, or not available.',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action') ||
                $listingProduct->isLockedObject($this->getActionIdentifier().'_action')) {

                // ->__('Another action is being processed. Try again when the action is completed.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Another action is being processed. Try again when the action is completed.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            $addingSku = $listingProduct->getChildObject()->getSku();
            empty($addingSku) && $addingSku = $listingProduct->getChildObject()->getAddingSku();

            if (empty($addingSku)) {

        // Parser hack -> Mage::helper('M2ePro')->__('Reference Code is not provided. Please, check Listing settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Reference Code is not provided. Please, check Listing settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            if (strlen($addingSku) > 26) {

           // Parser hack -> Mage::helper('M2ePro')->__('The length of Reference Code must be less than 26 characters.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'The length of Reference Code must be less than 26 characters.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            $tempListingsProducts[] = $listingProduct;
        }

        $tempListingsProducts2 = $this->checkOnlineSkuExistance($tempListingsProducts);

        $tempListingsProducts = array();

        foreach ($tempListingsProducts2 as $listingProduct) {

            if (!$this->checkGeneralConditions($listingProduct)) {
                continue;
            }

            $tempListingsProducts[] = $listingProduct;
        }

        return $tempListingsProducts;
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = array();

        $requestData['items'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $nativeData = Mage::getModel('M2ePro/Play_Connector_Product_Helper')
                                         ->getListRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        return $requestData;
    }

    // ########################################

    private function checkGeneralConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $addingGeneralId = $listingProduct->getChildObject()->getGeneralId();
        $addingGeneralIdType = $listingProduct->getChildObject()->getGeneralIdType();

        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER &&
            (empty($addingGeneralId) || empty($addingGeneralIdType))) {

        $message  = 'You can list a product only with assigned Play.com Identifier. ';
        $message .= 'Please, use the Search Play.com Identifier tool:  ';
        $message .= 'press the icon in Play.com Identifier column or choose appropriate command in the Actions dropdown.';
        $message .= ' Assigned Play.com Identifier will be displayed in Play.com Identifier column.';

            $this->addListingsProductsLogsMessage($listingProduct, $message,
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        empty($addingGeneralId) && $addingGeneralId = $listingProduct->getChildObject()->getAddingGeneralId();
        empty($addingGeneralIdType) && $addingGeneralIdType = $listingProduct->getChildObject()->getAddingGeneralIdType();

        if (empty($addingGeneralId) || empty($addingGeneralIdType)) {

            // Parser hack -> Mage::helper('M2ePro')->__('Identifier is not provided. Please, check Listing settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Identifier is not provided. Please, check Listing settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingCondition = $listingProduct->getChildObject()->getCondition();
        empty($addingCondition) && $addingCondition = $listingProduct->getChildObject()->getAddingCondition();

        $validConditions = $listingProduct->getGeneralTemplate()->getChildObject()->getConditionValues();

        if (empty($addingCondition) || !in_array($addingCondition,$validConditions)) {

            // ->__('Condition is invalid or missed. Please, check Listing Channel and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Condition is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingConditionNote = $listingProduct->getChildObject()->getConditionNote();
        if (is_null($addingConditionNote)) {
            $addingConditionNote = $listingProduct->getChildObject()->getAddingConditionNote();
        }

        if (is_null($addingConditionNote)) {

            // ->__('Comment is invalid or missed. Please, check Listing Channel and product settings.');
            $this->addListingsProductsLogsMessage(
            $listingProduct, 'Comment is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (!empty($addingConditionNote) && strlen($addingConditionNote) > 1000) {

            // ->__('The length of comment must be less than 1000 characters.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'The length of comment must be less than 1000 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $dispatchTo = $listingProduct->getChildObject()->getAddingDispatchTo();
        empty($dispatchTo) && $dispatchTo = $listingProduct->getChildObject()->getDispatchTo();

        $validDispatchTo = array(
            Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_BOTH,
            Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_UK,
            Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_EUROPA,
        );

        if (empty($dispatchTo) || !in_array($dispatchTo,$validDispatchTo)) {

            // ->__('Delivery Region is invalid or missed. Please, check Listing Channel and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Delivery Region is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $dispatchFrom = $listingProduct->getChildObject()->getAddingDispatchFrom();
        empty($dispatchFrom) && $dispatchFrom = $listingProduct->getChildObject()->getDispatchFrom();

        if (empty($dispatchFrom)) {

            // ->__('Dispatch Country is invalid or missed. Please, check Listing Channel and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Dispatch Country is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if ($dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_BOTH ||
            $dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_UK) {

            $priceGbr = $listingProduct->getChildObject()->getPriceGbr();

            if ($priceGbr <= 0) {

            // ->__('The price GBP must be greater than 0. Please, check the Selling Format Template and Product settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The price GBP must be greater than 0. Please, check the Selling Format Template and Product settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                return false;
            }
        }

        if ($dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_BOTH ||
            $dispatchTo == Ess_M2ePro_Model_Play_Template_General::DISPATCH_TO_EUROPA) {

            $priceEuro = $listingProduct->getChildObject()->getPriceEuro();

            if ($priceEuro <= 0) {

            // ->__('The price EUR must be greater than 0. Please, check the Selling Format Template and Product settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The price EUR must be greater than 0. Please, check the Selling Format Template and Product settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                return false;
            }
        }

        return true;
    }

    //-----------------------------------------

    private function checkOnlineSkuExistance($listingProducts)
    {
        $result = array();
        $listingProductsPacks = array_chunk($listingProducts,5,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            foreach ($listingProductsPack as $key => $listingProduct) {
                $skus[$key] = $listingProduct->getChildObject()->getAddingSku();
            }

            try {

                /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Server_Play_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Play_Connector')->getDispatcher();
                $response = $dispatcherObject->processVirtualAbstract('product','search','generalIdBySku',
                    array('items' => $skus),'items', $this->marketplace->getId(), $this->account->getId());

            } catch (Exception $exception) {

                Mage::helper('M2ePro/Exception')->process($exception);

                $this->addListingsLogsMessage(
                    reset($listingProductsPack), Mage::helper('M2ePro')->__($exception->getMessage()),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                return $result;
            }

            foreach($response as $key => $value) {
                if ($value === false || empty($value['general_id']) || empty($value['general_id_type'])) {
                    $result[] = $listingProductsPack[$key];
                } else {
                    $this->updateListingProduct($listingProductsPack[$key],
                                                $value['general_id'], $value['general_id_type']);
                }
            }
        }

        return $result;
    }

    private function updateListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, $generalId, $generalIdType)
    {
        $tempSku = $listingProduct->getChildObject()->getAddingSku();

        $data = array(
            'general_id' => $generalId,
            'general_id_type' => $generalIdType,
            'sku' => $tempSku,
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $listingProduct->addData($data)->save();

        $dataForAdd = array(
            'account_id' => $listingProduct->getListing()->getGeneralTemplate()->getAccountId(),
            'marketplace_id' => $listingProduct->getListing()->getGeneralTemplate()->getMarketplaceId(),
            'sku' => $tempSku,
            'product_id' => $listingProduct->getProductId(),
            'store_id' => $listingProduct->getListing()->getStoreId()
        );

        Mage::getModel('M2ePro/Play_Item')->setData($dataForAdd)->save();

        $message = Mage::helper('M2ePro')->__(
            'The product was found in your Play.com inventory and linked by Reference Code.'
        );

        $this->addListingsProductsLogsMessage(
            $listingProduct, $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    // ########################################
}