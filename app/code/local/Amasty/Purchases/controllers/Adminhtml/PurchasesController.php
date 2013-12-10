<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Purchases_Adminhtml_PurchasesController extends Mage_Adminhtml_Controller_Action
{
	public function productsAction() 
	{
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ampurchases/adminhtml_products')->toHtml()); 
	}
	
    public function ordersAction() 
	{
	    $this->getResponse()->setBody(
	       $this->getLayout()->createBlock('ampurchases/adminhtml_orders')->toHtml()); 
	}
	

    public function exportProductsCsvAction()
    {
        $this->export('products');
    }


    public function exportProductsXmlAction()
    {
        $this->export('products','xml');
    }
    	
    public function exportOrdersCsvAction()
    {
        $this->export('orders');
    }

    public function exportOrdersXmlAction()
    {
        $this->export('orders','xml');
    }	
    
    public function export($name, $type='csv')
    {
        $fileName   = $name . '.' . $type;
        $content    = $this->getLayout()->createBlock('ampurchases/adminhtml_' . $name);
        
        if ('csv' == $type)
            $content = $content->getCsvFile();
        else 
            $content = $content->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);        
    }
}