<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Helper/Data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ IMCecESwgDpUajgk('0e752f3f72cb5060111c7f6390e9ec77'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc.
*/
class Aitoc_Aitpreorder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Gets the order for particular item (if order exists)
     *
     * @param object $item
     * @return object
     */
    public function getOrder($item)
    {
        try
        {
            return $item->getOrder();
        }
        catch (Exception $e) {}

        return null;
    }

    /**
     * Inits product in a "right" way. Tries to add store id to product when loadinig it.
     *
     * @param object $item
     * @param string $sku
     * @return Mage_Catalog_Model_Product
     */
    public function initProduct($item, $sku = null)
    {
        $product = Mage::getModel('catalog/product');
        $order = $this->getOrder($item);

        if ($order)
        {
            $product->setStoreId($order->getStoreId());

            //FIX FOR WRONG STORE ID IN Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem::loadByProduct
            if (!Mage::registry('aitoc_order_refund_store_id'))
            {
                Mage::register('aitoc_order_refund_store_id', $order->getStoreId());
            }
        }

        $itemData = $item->getData();
        $productId = $sku ? $product->getIdBySku($sku) : $itemData['product_id'];
        $product->load($productId);
        return $product;
    }

    public function bundleHaveReg($_item)
    {
        $haveregular=0;
        $havePreorderInBundle=0;
        $bundleItems=$_item->getChildrenItems();
        foreach ($bundleItems as $bundleItem)
        {
            $original_product = $this->initProduct($bundleItem);
            if($original_product->getPreorder()==1)
            {
                $havePreorderInBundle=1;
            }

        }
        if($havePreorderInBundle==0)
        {
            $haveregular=1;
        }
        return $haveregular;
    }

    public function isHaveReg($_items,$ispending=0)
    {
        $haveregular            =   1;
        $noparent_item          =   0;
        $alldownloadable        =   1;
        $preorderdownloadable   =   0;
        $products_instock       =   0;

        foreach($_items as $_item)
        {
            $itemInOrderData    =   $_item->getData();
            $noparent_item      =   0;
            $isshiped           =   0;
            // if we here from frontend
            if(isset($itemInOrderData['qty_shipped']) && isset($itemInOrderData['qty_ordered']))
            {
                if(((int)($itemInOrderData['qty_shipped']))==((int)($itemInOrderData['qty_ordered'])))
                {
                    $isshiped=1;
                }
                if(!isset($itemInOrderData['parent_item_id']))
                {
                    $noparent_item=1;
                }
            }
            elseif(!isset($itemInOrderData['parent_item_id']))
            {
                $noparent_item=1;
            }

            if($isshiped==0)
            {
                //echo $itemInOrderData['product_type']."==";
                if($itemInOrderData['product_type']=='grouped')
                {
                    $alldownloadable    =   0;
                    $_product           =   $this->initProduct($_item);
                    $preorder           =   $_product->getPreorder();

                    if($preorder=='1')
                    {
                        $haveregular    =   0;
                    }
                }
                elseif($itemInOrderData['product_type']=='configurable')
                {
                    $alldownloadable    =   0;
                    $item_data          =   unserialize($_item->getData('product_options'));
                    $original_product   =   $this->initProduct($_item, $item_data['simple_sku']);
                    if ($original_product->getPreorder()==1)
                    {
                        $haveregular    =   0;
                    }
                }
                elseif($itemInOrderData['product_type']=='bundle')
                {
                    $alldownloadable=0;

                    if(Mage::helper('aitpreorder')->bundleHaveReg($_item) != '1')
                    {
                        $haveregular    =   0;
                    }

                }
                elseif(($itemInOrderData['product_type']=='virtual')&&($ispending==1)&&($noparent_item==1))
                {
                    $alldownloadable    =   0;
                    $haveregular        =   1;
                }
                elseif(($itemInOrderData['product_type']=='downloadable')&&($noparent_item==1))//&&($ispending==1))
                {
                    $_product   =   $this->initProduct($_item);
                    $preorder   =   $_product->getPreorder();
                    if($preorder!='1')
                    {
                        if($ispending==1)
                        {
                            $haveregular=1;
                        }
                    }
                    else
                    {
                        $preorderdownloadable=1;
                    }
                }
                elseif(($itemInOrderData['product_type']=='simple')&&($noparent_item==1))
                {
                    $alldownloadable        =   0;
                    $_product               =   $this->initProduct($_item);
                    $preorder               =   $_product->getPreorder();

                    if($preorder    ==  "1" && !$products_instock)
                    {
                        $haveregular        =   0;
                    } else {
                        $products_instock   =   1;
                        $haveregular        =   1;
                    }

                }
            }
        }

        if($ispending==0)
        {
            if(($alldownloadable==1)&&($preorderdownloadable==1))
            {
                $haveregular=-1;
            }
            elseif(($alldownloadable==1)&&($preorderdownloadable==0))
            {
                $haveregular=-2;
            }
        }

        #echo $alldownloadable.'##'.$preorderdownloadable.'##'.$haveregular.'<br>';

        return $haveregular;

    }


    public function isshipped($_items,$ispending=0)
    {
        $haveregular            =   1;
        $isshiped               =   0;

        foreach($_items as $_item)
        {
            $itemInOrderData    =   $_item->getData();
            $noparent_item      =   0;
            // if we here from frontend
            if(isset($itemInOrderData['qty_shipped']) && isset($itemInOrderData['qty_ordered']))
            {
                if(((int)($itemInOrderData['qty_shipped']))==((int)($itemInOrderData['qty_ordered'])))
                {
                    $isshiped=1;
                }
                if(!isset($itemInOrderData['parent_item_id']))
                {
                    $noparent_item=1;
                }
            }
            elseif(!isset($itemInOrderData['parent_item_id']))
            {
                $noparent_item=1;
            }

        }

        #echo $alldownloadable.'##'.$preorderdownloadable.'##'.$haveregular.'<br>';

        return $isshiped;

    }

    public function isHavePreorder($order)
    {
        $_items=$order->getItemsCollection();
        $haveregular=0;
        $noparent_item=0;
        $alldownloadable=1;
        $preorderdownloadable=0;
        $havepreorder=0;
        foreach($_items as $_item)
        {
            $itemInOrderData=$_item->getData();
            $noparent_item=0;
            if(!isset($itemInOrderData['parent_item_id']))
            {
                    $noparent_item=1;
            }

            if($itemInOrderData['product_type']=='grouped')
            {
                    $alldownloadable=0;
                    $_product = $this->initProduct($_item);
                    $preorder=$_product->getPreorder();
                    if($preorder!='1')
                    {
                        $haveregular=1;
                    }
                    else
                    {
                        $havepreorder=1;
                    }
            }
            elseif($itemInOrderData['product_type']=='configurable')
            {
                $alldownloadable=0;
                $item_data=unserialize($_item->getData('product_options'));
                $original_product = $this->initProduct($_item, $item_data['simple_sku']);
                if($original_product->getPreorder()!=1)
                {
                    $haveregular=1;
                }
                else
                {
                    $havepreorder=1;
                }
            }
            elseif($itemInOrderData['product_type']=='bundle')
            {
                $alldownloadable=0;
                if(Mage::helper('aitpreorder')->bundleHaveReg($_item)=='1')
                {
                    $haveregular=1;
                }
                else
                {
                    $havepreorder=1;
                }

            }
            elseif(($itemInOrderData['product_type']=='virtual')&&($noparent_item==1))
            {
                $alldownloadable=0;
                $haveregular=1;
            }
            elseif(($itemInOrderData['product_type']=='downloadable')&&($noparent_item==1))
            {
                $_product = $this->initProduct($_item);
                $preorder=$_product->getPreorder();
                if($preorder!='1')
                {
                    $haveregular=1;
                }
                else
                {
                    $preorderdownloadable=1;
                    $havepreorder=1;
                }
            }
            elseif(($itemInOrderData['product_type']=='simple')&&($noparent_item==1))
            {
                $alldownloadable=0;
                $_product = $this->initProduct($_item);
                $preorder=$_product->getPreorder();
                if($preorder!='1')
                {
                    $haveregular=1;
                }
                else
                {
                    $havepreorder=1;
                }
            }
        }

        return $havepreorder;

    }

    public function checkSynchronization($status,$statusPreorder)
    {

        if(!$statusPreorder)
        {
            return false;
        }
        if($status!=$statusPreorder)
        {
            if(!(($statusPreorder=='pendingpreorder' && $status=='pending')
            || ($statusPreorder=='processingpreorder' && $status=='processing')))
            {

                return false;
            }
        }
        return true;
    }

   /**
     * Checks if Aitoc Multi-Location Inventory module is enabled
     *
     * @return boolean
     */
    public function checkIfMultilocationInventoryIsEnabled()
    {
        $aitocModulesList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();

        if ($aitocModulesList)
        {
            foreach ($aitocModulesList as $aitocModule)
            {
                if ('Aitoc_Aitquantitymanager' == $aitocModule->getKey())
                {
                    return $aitocModule->isAvailable() && $aitocModule->getValue();
                }
            }
        }

        return false;
    }
} }
