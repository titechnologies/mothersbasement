<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Product_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingProductHelp');
        //------------------------------

        $this->setTemplate('M2ePro/buy/listing/product/help.phtml');
    }
}