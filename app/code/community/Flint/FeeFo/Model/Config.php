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
class Flint_Feefo_Model_Config extends Varien_Object
{
    const GENERAL = 'flint_feefo/general/';
    const HOME = 'flint_feefo/home/';
    const CATEGORY = 'flint_feefo/catalog/';
    const SEARCH   = 'flint_feefo/product/';

    public function getData($key='', $index=null)
    {
        if ( array_key_exists( $key, $this->_data ) ) {
            return $this->_data[ $key ];
        }
        return null;
    }

    public function getConfigData( $key, $default=false )
    {
	$key = $this->section . $key;
        if ( !$this->hasData( $key ) ) {
            $value = Mage::getStoreConfig( $key );
            if ( is_null( $value ) || false===$value ) {
                $value = $default;
            }
            $this->setData( $key, $value );
	}
        return $this->getData( $key );
    }


}