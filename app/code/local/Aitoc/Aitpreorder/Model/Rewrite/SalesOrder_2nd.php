<?php
/**
 * Product:     Pre-Order
 * Package:     Aitoc_Aitpreorder_1.1.26_425077
 * Purchase ID: JajOQtu3UxB8XoMt479nC9qGxjAzaifQKKovgevURc
 * Generated:   2012-11-07 12:17:45
 * File path:   app/code/local/Aitoc/Aitpreorder/Model/Rewrite/SalesOrder.data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpreorder')){ ReUZTOYpjrwQEajM('622d6c9578bef840022c56caac591f28'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc.
 */
class Aitoc_Aitpreorder_Model_Rewrite_SalesOrder extends Mage_Sales_Model_Order
{
    // overwrite parent
    public function addStatusHistoryCommentOLD($comment, $status = false)
    {
        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }

        $tmpstatus=$status;

        $order=$this;
        $orderStatus=$status;
        $haveregular=0;
echo $orderStatus;
exit;

        if(($orderStatus=='processingpreorder')||($orderStatus=='processing')||($orderStatus=='complete'))
        {
            $_items=$order->getItemsCollection();
            $haveregular=Mage::helper('aitpreorder')->isHaveReg($_items,0);

            if(($haveregular==1)&&($orderStatus=='processingpreorder' || $orderStatus =='processing'))
            {
                $tmpstatus='processing';
                $this->setStatusPreorder('processing');
            }
            elseif(($haveregular==0)&&(($orderStatus=='processing')))
            {
                $tmpstatus='processingpreorder';
                $this->setStatusPreorder('processingpreorder');
            }
            elseif($haveregular==-2 && $orderStatus=='processing')
            {
                $tmpstatus='processing';
                $this->setStatusPreorder('processing');
            }
            elseif($haveregular==-2 && $orderStatus!='processing')
            {
                $tmpstatus='complete';
                $this->setStatusPreorder('complete');
            }
            elseif($haveregular==-1)
            {
                $tmpstatus='processingpreorder';
                $this->setStatusPreorder('processingpreorder');
            }
        }
        elseif(($orderStatus=='pending')||($orderStatus=='pendingpreorder'))
        {

            $_items=$order->getItemsCollection();
                $haveregular=Mage::helper('aitpreorder')->isHaveReg($_items,1);

            if(($haveregular==0)&&($orderStatus=='pending'))
                    {
                $tmpstatus='pendingpreorder';
                $this->setStatusPreorder('pendingpreorder');
            }
                elseif(($haveregular!=0)&&($orderStatus=='pendingpreorder'))
                {
                    $tmpstatus='pending';
                    $this->setStatusPreorder('pending');
                }
        } else {

            $this->setStatusPreorder($orderStatus);
        }

        $history = Mage::getModel('sales/order_status_history')
            ->setStatus($tmpstatus)
            ->setComment($comment);
        $this->addStatusHistory($history);


        if($this->getStatus()=='pendingpreorder')
        {
            $this->setStatus('pending');
        }
        elseif($this->getStatus()=='processingpreorder')
        {
            $this->setStatus('processing');
        }


        return $history;
    }

    public function addStatusHistoryComment($comment, $status = false)
    {


        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }

        $tmpstatus      =   $status;
        $order          =   $this;
        $orderStatus    =   $status;
        $haveregular    =   0;



        if(($orderStatus=='processingpreorder')||($orderStatus=='processing')||($orderStatus=='complete'))
        {
            $_items             =   $order->getItemsCollection();
            $totalitems         =   sizeof($order->getItemsCollection());
            $haveregular        =   Mage::helper('aitpreorder')->isHaveReg($_items,0);
            $invoices_created   =   0;

            $invoices   = $this->getInvoiceCollection();

            foreach($invoices as $invoice)
            {
                $invoices_created = $invoices_created + sizeof($invoice->getAllItems());
            }



            if(($haveregular==1)&&($orderStatus=='processingpreorder' || $orderStatus =='processing'))
            {
                $tmpstatus  =   'processing';
                $this->setStatusPreorder('processing');
            }
            elseif(($haveregular==0)&&(($orderStatus=='processing')))
            {
                $tmpstatus  =   'processingpreorder';
                $this->setStatusPreorder('processingpreorder');
            }
            elseif(($haveregular==0)&&(($orderStatus=='processingpreorder')) && ($invoices_created > 0) && ($invoices_created < $totalitems))
            {

                $tmpstatus  =   'pendingpreorder'; // fixes
                $this->setStatusPreorder('pendingpreorder');  // fixes

            }
            elseif($haveregular==-2 && $orderStatus=='processing')
            {
                $tmpstatus  =   'processing';
                $this->setStatusPreorder('processing');
            }
            elseif($haveregular==-2 && $orderStatus!='processing')
            {
                $tmpstatus  =   'complete';
                $this->setStatusPreorder('complete');
            }
            elseif($haveregular==-1)
            {
                $tmpstatus='processingpreorder';
                $this->setStatusPreorder('processingpreorder');
            }
        }
        elseif(($orderStatus=='pending')||($orderStatus=='pendingpreorder'))
        {

            $_items         =   $order->getItemsCollection();
            $haveregular    =   Mage::helper('aitpreorder')->isHaveReg($_items,1);
            $isHavePreorder =   Mage::helper('aitpreorder')->isHavePreorder($order);

            $invoices_created   =   0;

            $invoices   = $this->getInvoiceCollection();
            foreach($invoices as $invoice)
            {
                $invoices_created = $invoices_created + sizeof($invoice->getAllItems());
            }


            if(($haveregular==0)&&($orderStatus=='pending'))
            {
                $tmpstatus          =   'pendingpreorder';
                $this->setStatusPreorder('pendingpreorder');

            } else if($isHavePreorder && $orderStatus=='pending' && ($invoices_created > 0) ) {

                $tmpstatus          =   'pendingpreorder';
                $this->setStatusPreorder('pendingpreorder');

            }elseif(($haveregular!=0)&&($orderStatus=='pendingpreorder')) {

                $tmpstatus      =   'pending';
                $this->setStatusPreorder('pending');
            }

        } else {
                $tmpstatus = $orderStatus;
                $this->setStatusPreorder($orderStatus);
        }


        $this->setStatus($tmpstatus);

        $history = Mage::getModel('sales/order_status_history')
            ->setStatus($tmpstatus)
            ->setComment($comment);
        $this->addStatusHistory($history);


        //if($this->getStatus()=='pendingpreorder')
        //{
         //   $this->setStatus('pending');
        //}
        //elseif($this->getStatus()=='processingpreorder')
        //{
         //   $this->setStatus('processing');
        //}

        return $history;
    }

   public function addStatusHistoryCommentNew($comment, $status = false)
    {
        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }

        $tmpstatus=$status;

        $order=$this;
        $orderStatus=$status;
        $haveregular=0;


        if(($orderStatus=='processingpreorder'))
        {
            $_items=$order->getItemsCollection();
            $haveregular    =   Mage::helper('aitpreorder')->isHaveReg($_items,0);


            if(($haveregular==0))
            {
                $tmpstatus  = 'pendingpreorder';
                $status     = 'pendingpreorder';
                $this->setStatusPreorder('pendingpreorder');
                $this->setStatus($status);
            }

/*
            if(($haveregular==1)&&($orderStatus=='processingpreorder' || $orderStatus =='processing'))
            {
                $tmpstatus='processing';
                $this->setStatusPreorder('processing');
            }
            elseif(($haveregular==0)&&(($orderStatus=='processing')))
            {
                $tmpstatus='processingpreorder';
                $this->setStatusPreorder('processingpreorder');
            }
            elseif($haveregular==-2 && $orderStatus=='processing')
            {
                $tmpstatus='processing';
                $this->setStatusPreorder('processing');
            }
            elseif($haveregular==-2 && $orderStatus!='processing')
            {
                $tmpstatus='complete';
                $this->setStatusPreorder('complete');
            }
            elseif($haveregular==-1)
            {
                $tmpstatus='processingpreorder';
                $this->setStatusPreorder('processingpreorder');
            }
    */
        }

        $history = Mage::getModel('sales/order_status_history')
            ->setStatus($status)
            ->setComment($comment)
            ->setEntityName($this->_historyEntityName);
        $this->addStatusHistory($history);
        return $history;
    }

   /**
     * Check order state before saving
     * Added for status change for invoice creation of pre-order items
     */

    protected function _checkState()
    {
        if (!$this->getId()) {
            return $this;
        }

        $userNotification = $this->hasCustomerNoteNotify() ? $this->getCustomerNoteNotify() : null;

        if (!$this->isCanceled()
            && !$this->canUnhold()
            && !$this->canInvoice()
            && !$this->canShip()) {
            if (0 == $this->getBaseGrandTotal() || $this->canCreditmemo()) {
                if ($this->getState() !== self::STATE_COMPLETE) {
                    $this->_setState(self::STATE_COMPLETE, true, '', $userNotification);
                }
            }
            /**
             * Order can be closed just in case when we have refunded amount.
             * In case of "0" grand total order checking ForcedCanCreditmemo flag
             */
            elseif (floatval($this->getTotalRefunded()) || (!$this->getTotalRefunded()
                && $this->hasForcedCanCreditmemo())
            ) {
                if ($this->getState() !== self::STATE_CLOSED) {
                    $this->_setState(self::STATE_CLOSED, true, '', $userNotification);
                }
            }
        }


        if ($this->getState() == self::STATE_NEW && $this->getIsInProcess()) {
            $this->setState(self::STATE_PROCESSING, true, '', $userNotification);
        } else if ($this->getStatus() == "pending"  && $this->getIsInProcess()) {

            $_items         =   $this->getItemsCollection();
            $isshipped      =   Mage::helper('aitpreorder')->isshipped($_items,0);


            if($isshipped == "1")
            $this->setState(self::STATE_PROCESSING, true, '', $userNotification);

        } else if($this->getStatus() == "pendingpreorder"  && $this->getIsInProcess()) {

            $invoices_created   = 0;
            $invoices           = $this->getInvoiceCollection();
            $totalitems         = sizeof($this->getItemsCollection());

            foreach($invoices as $invoice)
            {
                $invoices_created = $invoices_created + sizeof($invoice->getAllItems());
            }

            if($invoices_created == $totalitems)
            $this->setState('processing', 'processingpreorder', '', $userNotification);
        }

        return $this;
    }

} }

