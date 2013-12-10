<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/BundleCheckoutCartItemRenderer.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ ReUZTOYpjrwQEajM('aeb7a0f133c13a20eb7cf63ed2eeca16'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpreorder_Block_Rewrite_BundleCheckoutCartItemRenderer extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{
 
    public function getOptionList($useCache = true)
    {
        return $this->_getBundleOptions($useCache);
    }

    protected function _getBundleOptions($useCache = true)
    {
        $options = array();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $this->getProduct()->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption =  $this->getItem()->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = unserialize($optionsQuoteItemOption->getValue());
        if ($bundleOptionsIds) {
            /**
            * @var Mage_Bundle_Model_Mysql4_Option_Collection
            */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $this->getProduct());

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $this->getItem()->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue()),
                $this->getProduct()
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $option = array('label' => $bundleOption->getTitle(), "value" => array());
                    $bundleSelections = $bundleOption->getSelections();

                    foreach ($bundleSelections as $bundleSelection) {
                        $addinf='';
                        $selectionQty = $this->getProduct()->getCustomOption('selection_qty_' . $bundleSelection->getSelectionId());
                        $_product = Mage::getModel('catalog/product')->load($selectionQty->getProduct()->getId());
                        if($_product->getPreorder()=='1')
                        {
                            $preorderdescript='';
                            if($_product->getPreorderdescript()!="")
                            {
                                $preorderdescript=', '.$_product->getPreorderdescript();
                            }
                            $addinf='<p class="item-msg notice">* '.__('Pre-Order').$preorderdescript.'</p>';
                        }
                 
                        $option['value'][] = $this->_getSelectionQty($bundleSelection->getSelectionId()).' x '. $this->htmlEscape($bundleSelection->getName()). ' ' .Mage::helper('core')->currency($this->_getSelectionFinalPrice($bundleSelection)).$addinf;
                    }

                    $options[] = $option;
                }
            }
        }
        return $options;
    }

} } 