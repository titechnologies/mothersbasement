<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@meanbee.com so we can send you a copy immediately.
 *
 * @category   Meanbee
 * @package    Meanbee_Royalmail
 * @copyright  Copyright (c) 2012 Meanbee Ltd (http://www.meanbee.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Meanbee_Royalmail_Model_Shipping_Carrier_Royalmail_Surfacemail
    extends Meanbee_Royalmail_Model_Shipping_Carrier_Royalmail_Abstract {

    /**
     * Establish which rates to return
     *
     * @see http://www.royalmail.com/delivery/delivery-options-international/surface-mail/prices
     * @return rates
     */
    protected function getRates() {
        $country = strtoupper($this->_getCountry());

        if ($country != 'GB') {
            return $this->_loadCsv('surfacemail');
        }

        return null;
    }
}
