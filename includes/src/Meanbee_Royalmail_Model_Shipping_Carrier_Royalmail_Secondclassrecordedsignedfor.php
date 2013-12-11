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
 * @copyright  Copyright (c) 2008 Meanbee Internet Solutions (http://www.meanbee.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Meanbee_Royalmail_Model_Shipping_Carrier_Royalmail_Secondclassrecordedsignedfor
    extends Meanbee_Royalmail_Model_Shipping_Carrier_Royalmail_Secondclass {

    private $_extraCharge = 0.95;

    protected function getRates() {
        $rates = parent::getRates();

        for ($i = 0; $i < count($rates); $i++) {
            $rates[$i]['cost'] += $this->_extraCharge;
        }

        return $rates;
    }
}
