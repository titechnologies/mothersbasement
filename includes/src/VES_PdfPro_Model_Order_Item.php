<?php
/**
 * VES_PdfPro_Model_Order_Invoice_Item
 *
 * @author		VnEcoms Team <support@vnecoms.com>
 * @website		http://www.vnecoms.com
 */
class VES_PdfPro_Model_Order_Item extends Varien_Object
{
	/**
	 * Get Options of items
	 * @param Mage_Sales_Model_Order_Invoice_Item $item
	 * @return array:
	 */
	public function getItemOptions($item) {
		$result = array();
		if ($options = $item->getProductOptions()) {
			if (isset($options['options'])) {
				$result = array_merge($result, $options['options']);
			}
			if (isset($options['additional_options'])) {
				$result = array_merge($result, $options['additional_options']);
			}
			if (isset($options['attributes_info'])) {
				$result = array_merge($result, $options['attributes_info']);
			}
		}
		return $result;
	}
	/**
     * Getting all available childs for Invoice, Shipmen or Creditmemo item
     *
     * @param Varien_Object $item
     * @return array
     */
    public function getChilds($item)
    {
        $_itemsArray = array();

        if ($item instanceof Mage_Sales_Model_Order_Item) {
            $_items = $item->getOrder()->getAllItems();
        } else if ($item instanceof Mage_Sales_Model_Order_Invoice_Item) {
            $_items = $item->getInvoice()->getAllItems();
        } else if ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
            $_items = $item->getShipment()->getAllItems();
        } else if ($item instanceof Mage_Sales_Model_Order_Creditmemo_Item) {
            $_items = $item->getCreditmemo()->getAllItems();
        }

        if ($_items) {
            foreach ($_items as $_item) {
                $parentItem = $_item->getParentItem();
                if ($parentItem) {
                    $_itemsArray[$parentItem->getId()][$_item->getId()] = $_item;
                } else {
                    $_itemsArray[$_item->getId()][$_item->getId()] = $_item;
                }
            }
        }

        if (isset($_itemsArray[$item->getId()])) {
            return $_itemsArray[$item->getId()];
        } else {
            return null;
        }
    }

    /**
     * Retrieve is Shipment Separately flag for Item
     *
     * @param Varien_Object $item
     * @return bool
     */
    public function isShipmentSeparately($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }

            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    if (isset($options['shipment_type'])
                        && $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    if (isset($options['shipment_type'])
                        && $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }

        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['shipment_type'])
                && $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve is Child Calculated
     *
     * @param Varien_Object $item
     * @return bool
     */
    public function isChildCalculated($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }

            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    if (isset($options['product_calculations']) &&
                        $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    if (isset($options['product_calculations']) &&
                        $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD
                    ) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
        
        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve Bundle Options
     *
     * @param Varien_Object $item
     * @return array
     */
    public function getBundleOptions($item = null)
    {
        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['bundle_options'])) {
                return $options['bundle_options'];
            }
        }
        return array();
    }

    /**
     * Retrieve Selection attributes
     *
     * @param Varien_Object $item
     * @return mixed
     */
    public function getSelectionAttributes($item)
    {
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * Retrieve Order options
     *
     * @param Varien_Object $item
     * @return array
     */
    public function getOrderOptions($item = null)
    {
        $result = array();

        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Retrieve Order Item
     *
     * @return Mage_Sales_Order_Item
     */
    public function getOrderItem()
    {
    	if ($this->getItem() instanceof Mage_Sales_Model_Order_Item) {
            return $this->getItem();
        } else {
            return $this->getItem()->getOrderItem();
        }
    }

    /**
     * Retrieve Value HTML
     *
     * @param Mage_Sales_Order_Item $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $result = strip_tags($item->getName());
        if (!$this->isShipmentSeparately($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result =  sprintf('%d', $attributes['qty']) . ' x ' . $result;
            }
        }
        if (!$this->isChildCalculated($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result .= " " . strip_tags($this->getOrderItem()->getOrder()->formatPrice($attributes['price']));
            }
        }
        return $result;
    }

    /**
     * Can show price info for item
     *
     * @param Mage_Sales_Order_Item $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        if (($item->getParentItem() && $this->isChildCalculated())
                || (!$item->getParentItem() && !$this->isChildCalculated())) {
            return true;
        }
        return false;
    }	
}