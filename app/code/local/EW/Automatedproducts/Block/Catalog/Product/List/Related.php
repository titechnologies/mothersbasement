<?php
class EW_Automatedproducts_Block_Catalog_Product_List_Related extends Mage_Catalog_Block_Product_List_Related
{
	////////////////////////////////////////////////////////////////////////#1615
	//function to get matching product based on current product
	public function getMatchingProducts(){
		//the '4' used in this loop represtn four attributes ie. brand, license, series and type
		//this fout attributes are used to filter the result
		for($i=0;$i<4;$i++){
			$this->getProductsByConditionalAttributes($i);
			//$this->getItems()->getSize();

			foreach($this->getItems() as $_item){
				$tempArray[$_item->getSku()]['url'] = $_item->getProductUrl();
				$tempArray[$_item->getSku()]['name'] = $_item->getName();
				$tempArray[$_item->getSku()]['image'] = $_item->getImage();
				$tempArray[$_item->getSku()]['finalprice'] = $_item->getFinalPrice();
				$tempArray[$_item->getSku()]['sku'] = $_item->getSku();
				$tempArray[$_item->getSku()]['brand'] = $_item->getBrand();
				$tempArray[$_item->getSku()]['license'] = $_item->getLicense();
				$tempArray[$_item->getSku()]['series'] = $_item->getSeries();
				$tempArray[$_item->getSku()]['type'] = $_item->getType();
			}
		}//for ends

		//admin configuration settings 'admin-> system-> configurations-> catalog-> Automated Products-> Automated related products'
		$displayLimit = Mage::getStoreConfig('automatedproducts/relatedproducts/limit');

		//strip out duplicate products if any
		$tempFinalArray = $this->super_unique($tempArray,'sku');

		//slice array to limit display
		return array_slice($tempFinalArray, 0, $displayLimit); 
	}//function ends 'getMatchingProducts()'


	public function getProductsByConditionalAttributes($tempFlagCount){
		$helper = Mage::helper('automatedproducts');
		$config = $helper->getConfig("automatedproducts/relatedproducts");
		$conditions = $helper->getConditions("relatedproducts");
		$conditionArray = array();
		$store_id = Mage::app()->getStore()->getId();
		$product = Mage::registry('product');
		$product = Mage::getModel('catalog/product')->load($product->getId());
		$this->_itemCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect("*");
		$this->_itemCollection->setStoreId($store_id)->addStoreFilter($store_id);

		//conditional query
		if($tempFlagCount == 0){
			// filter by brand, license, series and type
			$this->_itemCollection->addAttributeToFilter('brand', array('eq' => $product->getBrand()));
			if($product->getLicense() != null){
				$this->_itemCollection->addAttributeToFilter('license', array('eq' => $product->getLicense()));
			}
			if($product->getSeries() != null){
				$this->_itemCollection->addAttributeToFilter('series', array('eq' => $product->getSeries()));
			}
			if($product->getType() != null){
				$this->_itemCollection->addAttributeToFilter('type', array('eq' => $product->getType()));
			}
		}else if($tempFlagCount == 1){
			// filter by brand, license and series
			$this->_itemCollection->addAttributeToFilter('brand', array('eq' => $product->getBrand()));
			if($product->getLicense() != null){
				$this->_itemCollection->addAttributeToFilter('license', array('eq' => $product->getLicense()));
			}
			if($product->getSeries() != null){
				$this->_itemCollection->addAttributeToFilter('series', array('eq' => $product->getSeries()));
			}
		}else if($tempFlagCount == 2){
			// filter by brand and license
			$this->_itemCollection->addAttributeToFilter('brand', array('eq' => $product->getBrand()));
			if($product->getLicense() != null){
				$this->_itemCollection->addAttributeToFilter('license', array('eq' => $product->getLicense()));
			}
		}else if($tempFlagCount == 3){
			// filter by brand only
			$this->_itemCollection->addAttributeToFilter('brand', array('eq' => $product->getBrand()));
		}else{
		/*do nothing*/
		}
		Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_itemCollection);
		$this->_itemCollection->addAttributeToFilter('visibility', array('in' => array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) ));

		$this->_itemCollection->addStoreFilter()
			->setPageSize((int)$config['limit'])
			//->setPageSize(10)
			->setCurPage(1)
			->addIdFilter(array($product->getId()), true);

		$this->_itemCollection->addAttributeToSort('name', 'asc');
		//$this->_itemCollection->getSelect()->order('rand()');
		$this->_itemCollection->getSelect();
		$this->_itemCollection->load();
		//return $this;
	}


	/// function to set unique a mutidimentional array
	function super_unique($array,$key){
	   $temp_array = array();
	   foreach ($array as &$v) {
	       if (!isset($temp_array[$v[$key]]))
	       $temp_array[$v[$key]] =& $v;
	   }
	   $array = array_values($temp_array);
	   return $array;
	}
	////////////////////////////////////////////////////////////////////////#1615
} 
?>
