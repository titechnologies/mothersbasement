<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral_Signup extends TBT_RewardsReferral_Model_Referral_Abstract {
    const STATUS_REFERRAL_SIGNED_UP = 1;

    public function getReferralStatusId() {
        return self::STATUS_REFERRAL_SIGNED_UP;
    }

    public function getReferralTransferMessage($newCustomer) {
        return Mage::getStoreConfig('rewards/transferComments/referralSignup');
    }

    public function getTotalReferralPoints() {
        $applicable_rules = Mage::getSingleton('rewardsref/validator')
                ->getApplicableRules(TBT_RewardsReferral_Model_Special_Signup::ACTION_REFERRAL_SIGNUP);
        $points = Mage::getModel('rewards/points');
        foreach ($applicable_rules as $arr) {
            $points->add($arr);
        }
        return $points;
    }

    public function getTransferReasonId() {
        return TBT_RewardsReferral_Model_Transfer_Reason_Signup::REASON_TYPE_ID;
    }

    /**
     * Referral Signup trigger
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @return TBT_RewardsReferral_Model_Referral_Abstract 
     */
    public function triggerEvent(Mage_Customer_Model_Customer $customer) {
        $this->loadByEmail($customer->getEmail());
        if (!$this->getReferralParentId()) {
            Mage::helper('rewardsref')->initateSessionReferral($customer);
            $this->loadByEmail($customer->getEmail());
            if (!$this->getReferralParentId()) {
                return $this;
            }
        }
        
        if(!$this->isValidParentWebsite())
        {
            return $this;
        }

        // update referral status
        $this->setReferralChildId($customer->getId());
        $this->setReferralStatus($this->getReferralStatusId());
        $this->save();

        $points = $this->getTotalReferralPoints();
        if ($points->isEmpty()) {
            return $this;
        }
        
        try {
            foreach ($points->getPoints() as $currencyId => $points_amount) {
                $transfer = Mage::getModel('rewardsref/transfer');
                $transferStatus = Mage::getStoreConfig('rewards/InitialTransferStatus/ReferralSignup');
                $transfer->create($points_amount, 
                        $currencyId,
                        $this->getReferralParentId(), 
                        $customer->getId(), 
                        $this->getReferralTransferMessage($customer), 
                        $this->getTransferReasonId(),
                        $transferStatus);
            }
            
            // send affiliate an email of the transaction
            $affiliate = $this->getParentCustomer();
            if ($affiliate->getRewardsrefNotifyOnReferral()) {
                $msg = $this->getReferralTransferMessage($customer);
                $this->sendConfirmation($affiliate, $customer->getEmail(), $customer->getName(), $msg, (string)$points);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

}