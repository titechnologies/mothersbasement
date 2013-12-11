<?php
/**
 * Flint Technology Ltd
 *
 * This module was developed by Flint Technology Ltd (http://www.flinttechnology.co.uk).
 * For support or questions, contact us via http://www.flinttechnology.co.uk/store/contacts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA bundled with this package in the file LICENSE.txt.
 * It is also available online at http://www.flinttechnology.co.uk/store/module-license-1.0
 *
 * @package     flint_feefo-ce-1.2.0.zip
 * @registrant  David Suter
 * @license     68561092-2FBC-43E2-8F1F-450A55AB97CE
 * @eula        Flint Module Single Installation License (http://www.flinttechnology.co.uk/store/module-license-1.0
 * @copyright   Copyright (c) 2012 Flint Technology Ltd (http://www.flinttechnology.co.uk)
 */

?>
<?php
class Flint_FeeFo_Model_Source_Since
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {  
            $statuses = Mage::getResourceModel('sales/order_status_collection')
                        ->orderByLabel();
                        //->toOptionArray();
            
            $options_default = array(
		  array('value' => 'year', 'label' => Mage::helper('flint_feefo')->__('One year')),
		  array('value' => '6month', 'label' => Mage::helper('flint_feefo')->__('6 months')),
		  array('value' => 'month', 'label' => Mage::helper('flint_feefo')->__('One month')),
		  array('value' => 'week', 'label' => Mage::helper('flint_feefo')->__('One week')),
		);
	    $this->_options = $options_default;

        }
        
        return $this->_options;
    }
    
    
}
