<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Play extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'marketplace',
        'synchronization',
        'account'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Play')->isActive();
    }

    // ########################################
}