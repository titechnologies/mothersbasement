<?php

class Scandi_Iwoca_Model_Observer
{
    public function addCustomLayoutHandle(Varien_Event_Observer $observer)
    {
        $controllerAction = $observer->getEvent()->getAction();
        $layout = $observer->getEvent()->getLayout();
        if ($controllerAction && $layout && $controllerAction instanceof Mage_Adminhtml_System_ConfigController) {
            if ($controllerAction->getRequest()->getParam('section') == 'iwoca') {
                $layout->getUpdate()->addHandle('iwoca_admin_handle');
            }
        }
        return $this;
    }

    public function dailyFeed()
    {
        if (Mage::helper('core')->isModuleEnabled('Scandi_Iwoca')) {

            $main = new stdClass();
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $prefix = Mage::getConfig()->getTablePrefix();
            $ordersTable = $prefix . 'sales_flat_order';
            $paymentsTable = $prefix . 'sales_flat_order_payment';
            $itemsTable = $prefix . 'sales_flat_order_item';

            $time = time();
            $to = date('Y-m-d H:i:s', $time);
            $lastTime = $time - 86400;
            $from = date('Y-m-d H:i:s', $lastTime);

            $account = (object) array('account_number' => Mage::getStoreConfig('iwoca/general/account'), 'password' => Mage::getStoreConfig('iwoca/general/password'));
            $main->credentials = $account;

            $data = new stdClass();
            $payments = Mage::getSingleton('payment/config')->getActiveMethods();
            foreach ($payments as $paymentCode=>$paymentModel) {
                $paymentRevenue = array();
                $query = "
                    SELECT
                        DATE(" . $ordersTable . ".created_at) AS date,
                        format(SUM(" . $ordersTable . ".total_paid),2) AS sales,
                        SUM(" . $ordersTable . ".total_item_count) AS items,
                        format(median.median_val,2) as median_item_price
                    FROM
                        " . $ordersTable . ", " . $paymentsTable . ", (
                          SELECT DATE(sq.created_at) as median_date, avg(sq.price) as median_val FROM (
                          SELECT t1.row_number, t1.price, t1.created_at, t1.order_id FROM(
                          SELECT IF(@prev!=DATE(d.created_at), @rownum:=1, @rownum:=@rownum+1) as 'row_number',
                          d.price, @prev:=DATE(d.created_at) AS created_at, d.order_id
                          FROM (
                            SELECT s.price, DATE(s.created_at) as created_at, m.entity_id, l.parent_id, l.method, s.order_id
                            FROM " . $itemsTable . " as s, " . $ordersTable . " as m, " . $paymentsTable . " as l
                            WHERE s.order_id = m.entity_id AND m.status = 'complete' AND m.entity_id = l.parent_id AND l.method = '" . $paymentCode . "' AND s.created_at BETWEEN '".$from."' AND '".$to."'
                            AND s.price > 0
                            ORDER BY created_at, price ASC ) as d,  (SELECT @rownum:=0, @prev:=NULL) r
                           ) as t1 INNER JOIN (
                        SELECT count(*) as total_rows, d.created_at
                        FROM " . $itemsTable . " d, " . $ordersTable . ", " . $paymentsTable . "
                        WHERE d.order_id = " . $ordersTable . ".entity_id AND " . $ordersTable . ".status = 'complete'
                        AND " . $ordersTable . ".entity_id = " . $paymentsTable . ".parent_id
                        AND " . $paymentsTable . ".method = '" . $paymentCode . "' AND d.created_at BETWEEN '".$from."' AND '".$to."'
                        AND d.price > 0
                        GROUP BY DATE(d.created_at) ) as t2
                        ON DATE(t1.created_at) = DATE(t2.created_at)
                        AND t1.row_number>=t2.total_rows/2 and t1.row_number<=t2.total_rows/2+1 )sq
                        GROUP BY DATE(sq.created_at)) as median
                    WHERE " . $ordersTable . ".status = 'complete'
                        AND " . $ordersTable . ".entity_id = " . $paymentsTable . ".parent_id
                        AND " . $paymentsTable . ".method = '" . $paymentCode . "'
                        AND " . $ordersTable . ".created_at BETWEEN '".$from."' AND '".$to."'
                        AND DATE(median.median_date) = DATE(" . $ordersTable . ".created_at)
                    GROUP BY DATE(" . $ordersTable . ".created_at) DESC";
                $results = $read->fetchAll($query);
                $methods = array(
                    'name'   => $paymentCode,
                    'revenue' => $results,
                );
                if (strpos($paymentCode,'paypal') !== false) {
                    $methods['username']= Mage::getStoreConfig('paypal/general/business_account');
                }
                if (strpos($paymentCode,'authorizenet_directpost') !== false) {
                    $methods['username']= Mage::getStoreConfig('payment/authorizenet_directpost/merchant_email');
                }
                if ($paymentCode == 'authorizenet') {
                    $methods['username']= Mage::getStoreConfig('payment/authorizenet/merchant_email');
                }
                if ($paymentCode == 'twocheckout_shared') {
                    $methods['username']= Mage::getStoreConfig('payment/twocheckout_shared/twoco_sid');
                }
                if ($paymentCode == 'tco') {
                    $methods['username']= Mage::getStoreConfig('payment/tco/username');
                }
                if ($paymentCode == 'worldpay_cc') {
                    $methods['username']= Mage::getStoreConfig('payment/worldpay_cc/inst_id');
                }
                $paymentProviders[] = $methods;
            }
            $data->payment_providers = $paymentProviders;

            foreach (Mage::app()->getWebsites() as $website) {
                $websiteRevenue = array();
                $store = $website->getDefaultStore();
                $url = new Mage_Core_Model_Url();
                $storePath = $url->getBaseUrl(array('_store'=> $store->getCode()));
                $currency = $store->getCurrentCurrencyCode();
                $storeId = $website->getId();
                $query = "
                    SELECT
                        DATE(" . $ordersTable . ".created_at) AS date,
                        format(SUM(" . $ordersTable . ".total_paid),2) AS sales,
                        SUM(" . $ordersTable . ".total_item_count) AS items,
                        format(median.median_val,2) as median_item_price
                    FROM
                        " . $ordersTable . ", (
                          SELECT DATE(sq.created_at) as median_date, avg(sq.price) as median_val FROM (
                          SELECT t1.row_number, t1.price, t1.created_at, t1.order_id FROM(
                          SELECT IF(@prev!=DATE(d.created_at), @rownum:=1, @rownum:=@rownum+1) as 'row_number',
                          d.price, @prev:=DATE(d.created_at) AS created_at, d.order_id
                          FROM (
                            SELECT s.price, DATE(s.created_at) as created_at, m.entity_id, s.order_id, s.store_id
                            FROM " . $itemsTable . " as s, " . $ordersTable . " as m
                            WHERE s.order_id = m.entity_id AND m.status = 'complete' AND s.store_id = '" . $storeId . "' AND s.created_at BETWEEN '".$from."' AND '".$to."'
                            AND s.price > 0
                            ORDER BY created_at, price ASC ) as d,  (SELECT @rownum:=0, @prev:=NULL) r
                           ) as t1 INNER JOIN (
                        SELECT count(*) as total_rows, d.created_at
                        FROM " . $itemsTable . " d, " . $ordersTable . " o
                        WHERE d.order_id = o.entity_id AND o.status = 'complete' AND o.store_id = '" . $storeId . "' AND d.store_id = '" . $storeId . "' AND o.store_id = d.store_id AND d.created_at BETWEEN '".$from."' AND '".$to."'
                        AND d.price > 0
                        GROUP BY DATE(d.created_at) ) as t2
                        ON DATE(t1.created_at) = DATE(t2.created_at)
                        AND t1.row_number>=t2.total_rows/2 and t1.row_number<=t2.total_rows/2+1 )sq
                        GROUP BY DATE(sq.created_at)) as median
                    WHERE " . $ordersTable . ".status = 'complete'
                        AND " . $ordersTable . ".store_id = '" . $storeId . "'
                        AND " . $ordersTable . ".created_at BETWEEN '".$from."' AND '".$to."'
                        AND DATE(median.median_date) = DATE(" . $ordersTable . ".created_at)
                    GROUP BY DATE(" . $ordersTable . ".created_at) DESC";
                $results = $read->fetchAll($query);
                $websiteArr = array(
                    'domain_name' => $storePath,
                    'currency' => $currency,
                    'revenue' => $results,
                );
                $webshops[] = $websiteArr;
            }
            $data->webshops = $webshops;
            $main->data = $data;
            $mainJson = json_encode($main, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

            $file = 'var/iwoca/account.php';
            if (file_get_contents($file) == Mage::getStoreConfig('iwoca/general/account')) {
                try {
                    if ($curl = curl_init()) {
                        curl_setopt($curl, CURLOPT_URL, 'http://www.iwoca.co.uk/api/magento/update/');
                        curl_setopt($curl, CURLOPT_HEADER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $mainJson);
                    } else {
                        throw new Exception($this->__('Error. Data could not be sent to iwoca. Connection error.'));
                    }
                    $res = curl_exec($curl);
                    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
                    $answer = substr($res, $header_size);
                    $this->getResponse()->setBody($answer);
                    Mage::log($answer);
                } catch(Exception $e) {
                    Mage::log($e->getMessage());
                }
                curl_close($curl);
            } else {
                Mage::log('Error. Incorrect iwoca account number.');
            }
        }
    }
}