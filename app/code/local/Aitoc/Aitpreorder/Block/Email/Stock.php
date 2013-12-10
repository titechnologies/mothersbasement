<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Email/Stock.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ fZQkNtSwaPpuOEae('3de39075e5452d34a231fbff6dbd6cc2'); ?><?php
/**
 * @copyright  Copyright (c) 2012 AITOC, Inc. 
 */
class Aitoc_Aitpreorder_Block_Email_Stock extends Mage_ProductAlert_Block_Email_Stock
{
    	
    /**
     * Retrive unsubscribe url for product
     *
     * @param int $productId
     * @return string
     */
    public function getProductUnsubscribeUrl($productId)
    {
        return '';
    }

    /**
     * Retrieve unsubscribe url for all products
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return '';
    }
	
	protected function _toHtml()
    {          
		$result=parent::_toHtml();
		if ($_products = $this->getProducts())
		{
		    foreach ($_products as $_product){
		        $toDelete = '<p><small><a href="' . $this->getProductUnsubscribeUrl($_product->getId()) . '">' . $this->__('Click here not to receive alerts for this product') . '</a></small></p>';

				$order_view_url = Mage::registry('order_view_url');
				if($order_view_url)
				{
					$product_url = $_product->getProductUrl();
					$result=str_replace($product_url, $order_view_url, $result);
				}
	            $result=str_replace($toDelete, '', $result);
			}
			
		}
		$toDelete2 = '<p><a href="' . $this->getUnsubscribeUrl() . '">' . $this->__('Unsubscribe from all stock alerts') . '</a></p>';
		$result=str_replace($toDelete2, '', $result);
		
		
		
		
		return $result;
    }
} } 