<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Block/Rewrite/CatalogSearchResult.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ fZQkNtSwaPpuOEae('6e12b9bf6cfc47ff8f2fab9e810993b6'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpreorder_Block_Rewrite_CatalogSearchResult extends Mage_CatalogSearch_Block_Result
{
    public function getResultCount()
    {
        #$this->_getProductCollection()->load();
        return parent::getResultCount();
    }
} } 