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

class Meanbee_Royalmail_Model_Shipping_Carrier_Royalmail
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'royalmail';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $freeBoxes = 0;
        $removeWeight = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getFreeShipping() && !$item->getProduct()->getTypeInstance()->isVirtual()) {
                    $freeBoxes += $item->getQty();
                    $removeWeight += $item->getWeight() * $item->getQty();
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $result = Mage::getModel('shipping/rate_result');

        if (count($this->getAllowedMethods()) > 0) {
            foreach ($this->getAllowedMethods() as $key => $value) {
                $obj = Mage::getModel("royalmail/shipping_carrier_royalmail_$key");
                
                if ($obj === false) {
                    Mage::log("Error loading royal mail: $key");
                    continue;
                }
                
                $obj->setWeightUnit($this->getConfigData('weight_unit'));

                $obj->setNegativeWeight($removeWeight);

                $cost = $obj->getCost($request);

                if ($cost !== null) {
                    $method = Mage::getModel('shipping/rate_result_method');

                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));

                    $method->setMethod($key);
                    $method->setMethodTitle($value);

                    if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                        $price = '0.00';
                    } else {
                        $price = $this->_performRounding($this->getFinalPriceWithHandlingFee($cost));
                    }

                    $method->setPrice($price);
                    $method->setCost($price);

                    $result->append($method);
                    
                    if ($price == '0.00') {
                        break; // No more free methods
                    }
                }
            }
        }

        return $result;
    }

    protected function _performRounding($number) {
        $old = $number;

        switch ($this->getConfigData('rounding_rule')) {
            case 'pound':
                $number = round($number);
                break;
            case 'pound-up':
                $number = ceil($number);
                break;
            case 'pound-down':
                $number = floor($number);
                break;
            case 'fifty':
                $number = round($number * 2) / 2;
                break;
            case 'fifty-up':
                $number = ceil($number * 2) / 2;
                break;
            case 'fifty-down':
                $number = floor($number * 2) / 2;
                break;
        }

        // Incase it rounds to 0
        if ($number == 0) {
            $number = ceil($old);
        }

        return $number;
    }

    public function getAllowedMethods() {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $this->getMethods($k);
        }
        return $arr;
    }

    public function getMethods($name=null) {
        $codes = array(
                // @see http://www.royalmail.com/delivery/business-delivery-options-uk/first-class-mail/prices
                'letter' => 'Letter',
                'largeletter' => 'Large Letter',
                
                // @see http://www.royalmail.com/delivery/business-delivery-options-uk/second-class-mail/prices
                'secondclass' => 'Second Class Packet',
                'secondclassrecordedsignedfor' => 'Second Class Packet (Recorded Signed for)',
                
                // @see http://www.royalmail.com/delivery/business-delivery-options-uk/first-class-mail/prices
                'firstclass' => 'First Class Packet',
                'firstclassrecordedsignedfor' => 'First Class Packet (Recorded Signed for)',
                
                // @see http://www.royalmail.com/delivery/business-delivery-options-uk/standard-parcels/prices
                'standardparcel' => 'Standard Parcel (Up to 46GBP Insurance)',
                'standardparcel100' => 'Standard Parcel (Up to 100GBP Insurance)',
                'standardparcel250' => 'Standard Parcel (Up to 250GBP Insurance)',
                'standardparcel500' => 'Standard Parcel (Up to 500GBP Insurance)',
                
                // @see http://www.royalmail.com/delivery/business-delivery-options-uk/special-delivery-next-day/prices
                'specialdeliverynextday500' => 'Special Delivery Next Day (Up to 500GBP Insurance)',
                'specialdeliverynextday1000' => 'Special Delivery Next Day (Up to 1,000GBP Insurance)',
                'specialdeliverynextday2500' => 'Special Delivery Next Day (Up to 2,500GBP Insurance)',
                
                // @see http://www.royalmail.com/delivery/business-delivery-options-uk/special-delivery-9am/prices
                'specialdelivery9am50' => 'Special Delivery 9.00 am (Up to 50GBP Insurance)',
                'specialdelivery9am1000' => 'Special Delivery 9.00 am (Up to 1,000GBP Insurance)',
                'specialdelivery9am2500' => 'Special Delivery 9.00 am (Up to 2,500GBP Insurance)',
                
                // @see http://www.royalmail.com/delivery/delivery-options-international/airmail/prices
                'airmail' => 'Airmail',
                
                // @see http://www.royalmail.com/delivery/delivery-options-international/airsure/prices
                'airsure' => 'Airsure (Up to 41GBP Insurance)',
                'airsureinsurance' => 'Airsure (Up to 250GBP/500GBP Insurance)',
                
                // @see http://www.royalmail.com/delivery/delivery-options-international/international-signed/prices
                'internationalsignedfor' => 'International Signed For',
                'internationalsignedforinsurance' => 'International Signed For (Up to 500GBP Insurance)',

                'surfacemail' => 'Surface Mail'
        );
        
        if ($name !== null) {
            if (isset($codes[$name])) {
                return $codes[$name];
            } else {
                return null;
            }
        } else {
            return $codes;
        }
    }
}
