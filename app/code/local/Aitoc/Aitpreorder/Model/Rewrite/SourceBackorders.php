<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Model/Rewrite/SourceBackorders.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ UgcjIPYiBZqTrDBy('e1881b6ce888fe7c0be884bc9f832391'); ?><?php
/**
 * @copyright  Copyright (c) 2011 AITOC, Inc.
 */
class Aitoc_Aitpreorder_Model_Rewrite_SourceBackorders extends Mage_CatalogInventory_Model_Source_Backorders
{
	const BACKORDERS_YES_PREORDERS = 30;
	public function toOptionArray()
    {
        $options = parent::toOptionArray();

		$options[] = array(
			'value' => self::BACKORDERS_YES_PREORDERS,
			'label'=>Mage::helper('cataloginventory')->__('Preorders')
		);

		return $options;
    }
}
 } ?>