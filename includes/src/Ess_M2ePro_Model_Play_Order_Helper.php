<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Order_Helper
{
    const PLAY_STATUS_PENDING  = 'Sale Pending';
    const PLAY_STATUS_SOLD     = 'Sold';
    const PLAY_STATUS_POSTED   = 'Posted';
    const PLAY_STATUS_CANCELED = 'Cancelled';

    const PLAY_SHIPPING_STATUS_INCOMPLETE = 'N';
    const PLAY_SHIPPING_STATUS_COMPLETED  = 'Y';

    public function getStatus($playStatus)
    {
        switch ($playStatus) {
            case self::PLAY_STATUS_SOLD:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_SOLD;
                break;
            case self::PLAY_STATUS_POSTED:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_POSTED;
                break;
            case self::PLAY_STATUS_CANCELED:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_CANCELED;
                break;
            case self::PLAY_STATUS_PENDING:
            default:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_PENDING;
                break;
        }

        return $status;
    }

    public function getShippingStatus($playShippingStatus)
    {
        switch ($playShippingStatus) {
            case self::PLAY_SHIPPING_STATUS_COMPLETED:
                $status = Ess_M2ePro_Model_Play_Order::SHIPPING_STATUS_COMPLETED;
                break;
            case self::PLAY_SHIPPING_STATUS_INCOMPLETE:
            default:
                $status = Ess_M2ePro_Model_Play_Order::SHIPPING_STATUS_INCOMPLETE;
                break;
        }

        return $status;
    }
}