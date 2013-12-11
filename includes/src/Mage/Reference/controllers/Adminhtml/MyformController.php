<?php

class Mage_Reference_Adminhtml_MyformController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
        $resource = Mage::getSingleton('core/resource');
     
    $readConnection = $resource->getConnection('core_read');
    $table = $resource->getTableName('sales_order_custom');

    
    $query = 'SELECT increment_id,DATE_FORMAT(created_at,"%d-%m-%Y") AS created_at, FORMAT(base_grand_total,2) AS total,CONCAT(customer_firstname," ",customer_lastname) AS credentials, GROUP_CONCAT(value) AS reference_value FROM sales_order_custom AS custom RIGHT JOIN sales_flat_order AS orders ON custom.order_id=orders.entity_id WHERE VALUE !="" GROUP BY increment_id ORDER BY increment_id DESC';

    $results = $readConnection->fetchAll($query);
     
    /**
     * Print out the results
     */
 
            $content = "Order id,Order date,Total,Customer name,Referrer, Referrer details";
            try {
                foreach ($results as $key => $value)
{
                $content .= "\n".$value['increment_id'].",".$value['created_at'].",".$value['total'].",".$value['credentials'].",".$value['reference_value'];
                //$content .= "\"{$product->getId()}\",\"{$product->getName()}\",\"{$product->getProductUrl()}\",\"{$product->getSku()}\"\n";
            }
              
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                //$this->_redirect('*/*/index');
            }
            $this->_prepareDownloadResponse('export.csv', $content, 'text/csv');
        
    }
    
}
