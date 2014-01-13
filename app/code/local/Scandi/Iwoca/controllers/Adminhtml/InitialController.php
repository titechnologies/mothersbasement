<?php
class Scandi_Iwoca_Adminhtml_InitialController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $main = new stdClass();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();
        $ordersTable = $prefix . 'sales_flat_order';
        $paymentsTable = $prefix . 'sales_flat_order_payment';
        $itemsTable = $prefix . 'sales_flat_order_item';

        $usersArray = Mage::getModel('admin/user')->getCollection();
        foreach ($usersArray as $user) {
            $roleId = $user->getRoles();
            $userArr = array (
                'role' => Mage::getModel('admin/roles')->load($roleId)->getRoleName(),
                'first_name' => $user->getFirstname(),
                'last_name' => $user->getLastname(),
                'email' => $user->getEmail()
            );
            $users['users'][] = $userArr;
        }
        $main->magento = $users;

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
                        WHERE s.order_id = m.entity_id AND m.status = 'complete' AND m.entity_id = l.parent_id AND l.method = '" . $paymentCode . "'
                        AND s.price > 0
                        ORDER BY created_at, price ASC ) as d,  (SELECT @rownum:=0, @prev:=NULL) r
                       ) as t1 INNER JOIN (
                    SELECT count(*) as total_rows, d.created_at
                    FROM " . $itemsTable . " d, " . $ordersTable . ", " . $paymentsTable . "
                    WHERE d.order_id = " . $ordersTable . ".entity_id AND " . $ordersTable . ".status = 'complete'
                    AND " . $ordersTable . ".entity_id = " . $paymentsTable . ".parent_id
                    AND " . $paymentsTable . ".method = '" . $paymentCode . "'
                    AND d.price > 0
                    GROUP BY DATE(d.created_at) ) as t2
                    ON DATE(t1.created_at) = DATE(t2.created_at)
                    AND t1.row_number>=t2.total_rows/2 and t1.row_number<=t2.total_rows/2+1 )sq
                    GROUP BY DATE(sq.created_at)) as median
                WHERE " . $ordersTable . ".status = 'complete'
                    AND " . $ordersTable . ".entity_id = " . $paymentsTable . ".parent_id
                    AND " . $paymentsTable . ".method = '" . $paymentCode . "'
                    AND DATE(median.median_date) = DATE(" . $ordersTable . ".created_at)
                GROUP BY DATE(" . $ordersTable . ".created_at) DESC";
            $results = $read->fetchAll($query);
            $methods = array(
                'name'   => $paymentCode,
                'revenue' => $results,
            );
            if (strpos($paymentCode,'paypal') !== false) {
                $acc = Mage::getStoreConfig('paypal/general/business_account');
                $methods['username']= $acc;
            }
            if (strpos($paymentCode,'authorizenet_directpost') !== false) {
                $acc = Mage::getStoreConfig('payment/authorizenet_directpost/merchant_email');
                $methods['username']= $acc;
            }
            if ($paymentCode =='authorizenet') {
                $acc = Mage::getStoreConfig('payment/authorizenet/merchant_email');
                $methods['username']= $acc;
            }
            if ($paymentCode =='twocheckout_shared') {
                $acc = Mage::getStoreConfig('payment/twocheckout_shared/twoco_sid');
                $methods['username']= $acc;
            }
            if ($paymentCode =='tco') {
                $acc = Mage::getStoreConfig('payment/tco/username');
                $methods['username']= $acc;
            }
            if ($paymentCode =='worldpay_cc') {
                $acc = Mage::getStoreConfig('payment/worldpay_cc/inst_id');
                $methods['username']= $acc;
            }
            $paymentProviders[] = $methods;
        }
        $main->payment_providers = $paymentProviders;

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
                        WHERE s.order_id = m.entity_id AND m.status = 'complete' AND s.store_id = '" . $storeId . "'
                        AND s.price > 0
                        ORDER BY created_at, price ASC ) as d,  (SELECT @rownum:=0, @prev:=NULL) r
                       ) as t1 INNER JOIN (
                    SELECT count(*) as total_rows, d.created_at
                    FROM " . $itemsTable . " d, " . $ordersTable . " o
                    WHERE d.order_id = o.entity_id AND o.status = 'complete' AND o.store_id = '" . $storeId . "' AND d.store_id = '" . $storeId . "' AND o.store_id = d.store_id
                    AND d.price > 0
                    GROUP BY DATE(d.created_at) ) as t2
                    ON DATE(t1.created_at) = DATE(t2.created_at)
                    AND t1.row_number>=t2.total_rows/2 and t1.row_number<=t2.total_rows/2+1 )sq
                    GROUP BY DATE(sq.created_at)) as median
                WHERE " . $ordersTable . ".status = 'complete'
                    AND " . $ordersTable . ".store_id = '" . $storeId . "'
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
        $main->webshops = $webshops;

        $business = array();
        $main->business = $business;

        $mainJson = json_encode($main, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        if (is_writable(Mage::getBaseDir('var')) || is_writable(Mage::getBaseDir('base'))) {

            try {

                if ($curl = curl_init()) {
                    curl_setopt($curl, CURLOPT_URL, 'http://www.iwoca.co.uk/api/magento/create');
                    curl_setopt($curl, CURLOPT_HEADER, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $mainJson);
                } else {
                    throw new Exception($this->__('Error. Data could not be sent to iwoca. Connection error.'));
                }

                $res = curl_exec($curl);

                $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
                $answer = substr($res, $headerSize);

                $answerArr = (array) json_decode($answer);
                $accountNumber = $answerArr["account_number"];
                $confirmation = $answerArr["auth_token"];
                $password = $answerArr["password"];

                $coreConfig = new Mage_Core_Model_Config();
                $coreConfig->saveConfig('iwoca/general/account', $accountNumber, 'default', 0);
                $coreConfig->saveConfig('iwoca/general/password', $password, 'default', 0);
                $coreConfig->saveConfig('iwoca/general/confirm', $confirmation, 'default', 0);

                $dir = 'var/iwoca';
                if (!file_exists ( $dir )) {
                    mkdir($dir);
                }

                $safeFile = "var/iwoca/account.php";
                $fileWrite = fopen($safeFile, 'w');
                fwrite($fileWrite, $accountNumber);
                fclose($fileWrite);

                $safeFile2 = "iwoca_ext_confirm.php";
                $fileWrite2 = fopen($safeFile2, 'w');
                fwrite($fileWrite2, $confirmation);
                fclose($fileWrite2);

                $emailTemplate  = Mage::getModel('core/email_template')->loadDefault('iwoca_email_template');
                $emailTemplateVariables = array();
                $emailTemplateVariables['account_number'] = $accountNumber;
                $emailTemplateVariables['confirmation'] = $confirmation;
                $emailTemplateVariables['password'] = $password;
                $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
                $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
                $emailTemplate->setTemplateSubject('Iwoca Account Information');
                $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
                $emailTemplate->send(Mage::getStoreConfig('trans_email/ident_general/email'),Mage::getStoreConfig('trans_email/ident_general/name'), $emailTemplateVariables);

                $final =
                    '<div class="comment">'
                        . Mage::helper('iwoca')->__('Copy these account identifiers back to your iwoca profile. You&#39;ll find fields for them under step 4 of the instructions to link your account.') .
                    '</div>
                    <table class="form-list iwoca" id="iwoca-active"><tbody><tr>
                        <td class="label"><label for="iwoca_general_account">' . Mage::helper('iwoca')->__('Account Number') . '</label></td>
                        <td class="value"><input type="text" class="input-text" readonly="readonly" value="' . $accountNumber . '" name="iwoca_general_account" id="iwoca_general_account" />
                        <div id="copy-acc2" class="copy-button" data-clipboard-target="iwoca_general_account" data-clipboard-text="Copy" title="Click to copy account number">' . Mage::helper('iwoca')->__('Copy') . '</div></td>
                    </tr><tr>
                        <td class="label"><label for="iwoca_general_password">' . Mage::helper('iwoca')->__('Password') . '</label></td>
                        <td class="value"><input type="text" class="input-text" readonly="readonly" value="' . $password . '" name="iwoca_general_password" id="iwoca_general_password" />
                        <div id="copy-pass2" class="copy-button" data-clipboard-target="iwoca_general_password" data-clipboard-text="Copy" title="Click to copy password">' . Mage::helper('iwoca')->__('Copy') . '</div></td>
                    </tr></tbody></table>';

                $this->getResponse()->setBody($final);

            } catch(Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
                Mage::log('Error. Data could not be sent to iwoca. Connection error.');
            }

            curl_close($curl);

        } else {
            $this->getResponse()->setBody($this->__('Permission error. Could not create folder on server. Please, set permissions to root or/and var folders.'));
        }
    }
}