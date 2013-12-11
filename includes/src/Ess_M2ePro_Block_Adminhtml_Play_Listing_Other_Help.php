<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Play_Listing_Other_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingOtherHelp');
        //------------------------------

        $this->setTemplate('M2ePro/play/listing/other/help.phtml');
    }

    public function getContainerId()
    {
        return 'block_notice_play_listings';
    }
}