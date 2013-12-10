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

class Meanbee_Royalmail_Model_Roundingrule {
    public function toOptionArray() {
        $options = array(
            array('value' => 'none', 'label' => 'No rounding performed'),
            array('value' => 'pound', 'label' => 'Round to the nearest pound'),
            array('value' => 'pound-up', 'label' => 'Round to the next whole pound'),
            array('value' => 'pound-down', 'label' => 'Round to the previous whole pound'),
            array('value' => 'fifty', 'label' => 'Round to the nearest 50p or whole pound'),
            array('value' => 'fifty-up', 'label' => 'Round to the next 50p or whole pound'),
            array('value' => 'fifty-down', 'label' => 'Round to the previous 50p or whole pound')
        );

        return $options;
    }
}
