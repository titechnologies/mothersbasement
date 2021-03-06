<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_GeneralController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro');
    }

    //#############################################

    public function validationCheckRepetitionValueAction()
    {
        $model = $this->getRequest()->getParam('model','');

        $dataField = $this->getRequest()->getParam('data_field','');
        $dataValue = $this->getRequest()->getParam('data_value','');

        if ($model == '' || $dataField == '' || $dataValue == '') {
            exit(json_encode(array('result'=>false)));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();

        if ($dataField != '' && $dataValue != '') {
            $collection->addFieldToFilter($dataField, array('in'=>array($dataValue)));
        }

        $idField = $this->getRequest()->getParam('id_field','id');
        $idValue = $this->getRequest()->getParam('id_value','');

        if ($idField != '' && $idValue != '') {
            $collection->addFieldToFilter($idField, array('nin'=>array($idValue)));
        }

        exit(json_encode(array('result'=>!(bool)$collection->getSize())));
    }

    //#############################################

    public function synchCheckStateAction()
    {
        $lockItemModel = Mage::getModel('M2ePro/Synchronization_LockItem');

        if ($lockItemModel->isExist()) {
            exit('executing');
        }

        exit('inactive');
    }

    public function synchGetLastResultAction()
    {
        $logsModel = Mage::getModel('M2ePro/Synchronization_Log');
        $runsModel = Mage::getModel('M2ePro/Synchronization_Run');

        $tempCollection = $logsModel->getCollection();
        $tempCollection->addFieldToFilter('synchronization_run_id', (int)$runsModel->getLastId());
        $tempCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR)));

        if ($tempCollection->getSize() > 0) {
            exit('error');
        }

        $tempCollection = $logsModel->getCollection();
        $tempCollection->addFieldToFilter('synchronization_run_id', (int)$runsModel->getLastId());
        $tempCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING)));

        if ($tempCollection->getSize() > 0) {
            exit('warning');
        }

        exit('success');
    }

    public function synchGetExecutingInfoAction()
    {
        $response = array(
            'mode' => 'executing'
        );

        $lockItemModel = Mage::getModel('M2ePro/Synchronization_LockItem');

        if (!$lockItemModel->isExist()) {
            $response['mode'] = 'inactive';
            exit(json_encode($response));
        }

        $response['title'] = $lockItemModel->getContentData('info_title');

        $response['percents'] = (int)$lockItemModel->getContentData('info_percents');
        $response['percents'] < 0 && $response['percents'] = 0;

        $response['status'] = $lockItemModel->getContentData('info_status');

        exit(json_encode($response));
    }

    //#############################################

    public function modelGetAllAction()
    {
        $model = $this->getRequest()->getParam('model','');
        $componentMode = $this->getRequest()->getParam('component_mode', '');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $idField == '' || $dataField == '') {
            exit(json_encode(array()));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $componentMode != '' && $collection->addFieldToFilter('component_mode', $componentMode);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field','');
        $sortDir = $this->getRequest()->getParam('sort_dir','ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.'.$sortField,$sortDir);
        }

        $limit = $this->getRequest()->getParam('limit',NULL);
        !is_null($limit) && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        exit(json_encode($data['items']));
    }

    public function modelGetAllByAttributeSetIdAction()
    {
        $model = $this->getRequest()->getParam('model','');
        $componentMode = $this->getRequest()->getParam('component_mode', '');
        $attributeSets = $this->getRequest()->getParam('attribute_sets','');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $attributeSets == '' || $idField == '' || $dataField == '') {
            exit(json_encode(array()));
        }

        $templateType = 0;
        switch ($model) {
            case 'Template_SellingFormat':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
                break;
            case 'Template_Description':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
                break;
            case 'Template_General':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL;
                break;
        }

        $tasTable = Mage::getResourceModel('M2ePro/AttributeSet')->getMainTable();

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $componentMode != '' && $collection->addFieldToFilter('component_mode', $componentMode);

        $attributeSets = explode(',', $attributeSets);

        $collection->getSelect()
                   ->join(array('tas'=>$tasTable),'`main_table`.`'.$idField.'` = `tas`.`object_id`',array())
                   ->where('`tas`.`object_type` = ?',(int)$templateType)
                   ->group('main_table.'.$idField)
                   ->having('COUNT(`main_table`.`'.$idField.'`) >= ?', count($attributeSets));

        $collection->addFieldToFilter('`tas`.`attribute_set_id`', array('in' => $attributeSets));

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field','');
        $sortDir = $this->getRequest()->getParam('sort_dir','ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.'.$sortField,$sortDir);
        }

        $limit = $this->getRequest()->getParam('limit',NULL);
        !is_null($limit) && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        exit(json_encode($data['items']));
    }

    //#############################################

    public function searchAutocompleteAction()
    {
        $model       = $this->getRequest()->getParam('model');
        $component   = $this->getRequest()->getParam('component');
        $queryString = $this->getRequest()->getParam('query');
        $maxResults  = (int) $this->getRequest()->getParam('maxResults');

        if (!$model || !$component || !$queryString || !$maxResults) {
            exit(json_encode(array()));
        }

        $where = array();
        $parts = explode(' ', $queryString);
        foreach ($parts as $part) {
            $part = trim($part);
            if (!$part) {
                continue;
            }
            $where[]['like'] = "%$part%";
        }

        if (empty($where)) {
            exit(json_encode(array()));
        }

        $quotedQueryString = addslashes(trim($queryString));

        $relevanceQueryString  = "IF( `main_table`.`title` LIKE '%". $quotedQueryString. "%', ";
        $relevanceQueryString .= substr_count($quotedQueryString, " ") + 1;
        $relevanceQueryString .= "*3, 0) + IF( `main_table`.`title` LIKE '%";
        $relevanceQueryString .= str_replace(" ", "%', 1, 0) + IF( `main_table`.`title` LIKE '%", $quotedQueryString);
        $relevanceQueryString .= "%', 1 , 0)";

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($component, $model)
            ->getCollection()
            ->addFieldToFilter("`main_table`.`title`", $where)
            ->setOrder('relevance', 'DESC');

        $collection->getSelect()->columns(array('relevance' => new Zend_Db_Expr($relevanceQueryString)));

        $quantity = $collection->getSize();
        $collection->getSelect()->limit($maxResults);
        $results = $collection->getData();

        $suggestions = array();
        $ids         = array();

        foreach ($results as $result) {
            $suggestions[] = $result['title'];
            $ids[] = $result['id'];
        }
        $array = array(
            'query'       => $queryString,
            'suggestions' => $suggestions,
            'data'        => $ids,
            'quantity'    => $quantity
        );
        exit(json_encode($array));
    }

    public function searchAutocompleteByAttributeSetIdAction()
    {
        $idField     = $this->getRequest()->getParam('id_field','id');
        $model       = $this->getRequest()->getParam('model');
        $component   = $this->getRequest()->getParam('component');
        $queryString = $this->getRequest()->getParam('query');
        $maxResults  = (int) $this->getRequest()->getParam('maxResults');
        $attributeSets = $this->getRequest()->getParam('attribute_sets');

        if (!$model || !$component || !$queryString || !$maxResults || !$attributeSets) {
            exit(json_encode(array()));
        }

        $where = array();
        $parts = explode(' ', $queryString);
        foreach ($parts as $part) {
            $part = trim($part);
            if (!$part) {
                continue;
            }
            $where[]['like'] = "%$part%";
        }

        if (empty($where)) {
            exit(json_encode(array()));
        }

        $quotedQueryString = addslashes(trim($queryString));
        $relevanceQueryString  = "IF( `main_table`.`title` LIKE '%". $quotedQueryString. "%', ";
        $relevanceQueryString .= substr_count($quotedQueryString, " ") + 1;
        $relevanceQueryString .= "*3, 0) + IF( `main_table`.`title` LIKE '%";
        $relevanceQueryString .= str_replace(" ", "%', 1, 0) + IF( `main_table`.`title` LIKE '%", $quotedQueryString);
        $relevanceQueryString .= "%', 1 , 0)";

        $templateType = 0;
        switch ($model) {
            case 'Template_SellingFormat':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
                break;
            case 'Template_Description':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
                break;
            case 'Template_General':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL;
                break;
        }

        $tasTable = Mage::getResourceModel('M2ePro/AttributeSet')->getMainTable();

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($component, $model)
            ->getCollection()
            ->addFieldToFilter("`main_table`.`title`", $where);

        $collection->getSelect()->columns(array('relevance' => new Zend_Db_Expr($relevanceQueryString)));

        $collection->getSelect()
            ->join(array('tas'=>$tasTable),'`main_table`.`'.$idField.'` = `tas`.`object_id`',array())
            ->where('`tas`.`object_type` = ?',(int)$templateType);

        $attributeSets = explode(',', $attributeSets);
        $collection->addFieldToFilter('`tas`.`attribute_set_id`', array('in' => $attributeSets));

        $collection->getSelect()
                   ->group('main_table.'.$idField)
                   ->having('COUNT(`main_table`.`'.$idField.'`) >= ?', count($attributeSets));

        $results = $collection->setOrder('relevance', 'DESC')->getData();
        $quantity = count($results);

        $suggestions = array();
        $ids         = array();

        $results = array_slice($results,0,$maxResults);

        foreach ($results as $result) {
            $suggestions[] = $result['title'];
            $ids[] = $result['id'];
        }
        $array = array(
            'query'       => $queryString,
            'suggestions' => $suggestions,
            'data'        => $ids,
            'quantity'    => $quantity
        );
        exit(json_encode($array));
    }

    //#############################################

    public function magentoGetAttributesByAttributeSetsAction()
    {
        $attributeSets = $this->getRequest()->getParam('attribute_sets','');

        if ($attributeSets == '') {
            exit(json_encode(array()));
        }

        $attributeSets = explode(',',$attributeSets);

        if (!is_array($attributeSets) || count($attributeSets) <= 0) {
            exit(json_encode(array()));
        }

        exit(json_encode(
            Mage::helper('M2ePro/Magento')->getAttributesByAttributeSets($attributeSets)
        ));
    }

    //#############################################

    public function magentoRuleGetNewConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $prefix = $this->getRequest()->getParam('prefix');
        $storeId = $this->getRequest()->getParam('store', 0);
        $attributeCriteria = $this->getRequest()->getParam(
            'attribute_criteria', Ess_M2ePro_Model_Magento_Product_Rule::LOAD_ATTRIBUTES_CRITERIA_ALL
        );
        $attributeSets = $this->getRequest()->getParam('attribute_sets');
        if (empty($attributeSets)) {
            $attributeSets = array();
        } else {
            $attributeSets = explode(',', $attributeSets);
        }

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $rule = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(array(
            'attribute_criteria' => $attributeCriteria,
            'attribute_sets' => $attributeSets
        ));

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule($rule)
            ->setPrefix($prefix);

        if ($type == 'M2ePro/Magento_Product_Rule_Condition_Combine') {
            $model->setData($prefix, array());
        }

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($prefix);
            $model->setStoreId($storeId);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function getRuleConditionChooserHtmlAction()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_magento_product_rule_chooser_sku',
                    'product_rule_chooser_sku',
                    array(
                        'js_form_object' => $request->getParam('form'),
                        'store' => $request->getParam('store', 0)
                    )
                );
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', array());
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int) $id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = array();
                }

                $block = $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_magento_product_rule_chooser_category',
                    'promo_widget_chooser_category_ids',
                    array('js_form_object' => $request->getParam('form'))
                )->setCategoryIds($ids);
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function categoriesJsonAction()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');

        $category   = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }

    //#############################################
}