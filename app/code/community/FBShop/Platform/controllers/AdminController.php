<?php

/**
 * Admin menu
 *
 * @category   FBShop
 * @package    FBSho_Platform
 */
class FBShop_Platform_AdminController extends Mage_Adminhtml_Controller_Action {

    public function testAction() {
        Mage::getSingleton('core/session')->addNotice('uguu');
        $this->loadLayout();
    }

    public function indexAction() {
        try {
//http://dev.fbshops.org/ShopPlatform/magento/validation/plugin?host=http://127.0.0.1/theelook/lionette/

            $model = new FBShop_Platform_Model_Platform();
            $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            
            $url = $model->host."/magento/validation/plugin?host=" . $host;
            $obj=array();
            try {
                $var = new Mage_HTTP_Client_Curl();
                $var->get($url);
               
                $obj = (array) json_decode($var->getBody(), true);
                
                            } catch (Exception $e) {
            }
                if ($obj["status"]==true){
                    
               
            $pluginReturnUrl = Mage::getModel('adminhtml/url')->getUrl('FBShop-installation/admin');
            $this->loadLayout();
            $this->_setActiveMenu('FBShop');
            $host = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                  $model = Mage::getSingleton('platform/platform')->loadShop();
               if ($model!=false) { 
                   
                if ($model->getId() != NULL) {

                    $token = $model->getUser() . '.' . $model->getPassword();
                    $model = new FBShop_Platform_Model_Platform();
                    $block = $this->getLayout()
                            ->createBlock('core/text', 'FBShop-admin')
                            ->setText('<script>
function setHeight() {
    parent.document.getElementById("iframeId").height = 1000;
   if (document.location.hostname == "localhost")
{
     alert("Works only on external IP")
}
}
</script>         
<iframe id="iframeId" style="border:0px" src="' . $model->host . '/plugin/login?token=' . $token . '"
    
" width="100%" onload="setHeight()">
  <p>Your browser does not support iframes.</p>
</iframe>');
                
            } }
            else {
                $model = Mage::getModel('platform/platform');
                $model->execute();

                $token = base64_encode($model->userApi) . '.' . base64_encode($model->pswApi);

                $model = new FBShop_Platform_Model_Platform();
                $block = $this->getLayout()
                        ->createBlock('core/text', 'FBShop-admin')
                        ->setText('<script>
function setHeight() {
    parent.document.getElementById("iframeId").height = 1000;
  if (document.location.hostname == "localhost")
{
     alert("Works only on external IP")
}
}
</script>         
<iframe id="iframeId" style="border:0px" src="' . $model->host . '/registration/create_user?hostUrl=' . $host . '&token=' . $token . '&pluginReturnUrl=' . $pluginReturnUrl . '" width="100%" onload="setHeight()">
  <p>Your browser does not support iframes.</p>
</iframe>');
            }
              $this->_addContent($block);
               $this->renderLayout();
            
            } elseif($obj["status"]==false) {
                    
           
            echo Mage::getSingleton('core/session')->addError("Error! You are using local host, or your IP is not accessible from outside, please contact your hosting provider.");
                $this->loadLayout();
                $this->renderLayout();
                return;
                }
        } catch (Exception $e) {
            
        }

      
       
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('FBShop');
    }

    function ip_is_private($ip) {
        $pri_addrs = array(
            '10.0.0.0|10.255.255.255',
            '172.16.0.0|172.31.255.255',
            '192.168.0.0|192.168.255.255',
            '169.254.0.0|169.254.255.255',
            '127.0.0.0|127.255.255.255'
        );

        $long_ip = ip2long($ip);
        if ($long_ip != -1) {
            foreach ($pri_addrs AS $pri_addr) {
                list($start, $end) = explode('|', $pri_addr);

                // IF IS PRIVATE
                if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end))
                    return (TRUE);
            }
        }
        return (FALSE);
    }

    function is_ipv6($ip) {
        // If it contains anything other than hex characters, periods, colons or a / it's not IPV6
        if (!preg_match("/^([0-9a-f\.\/:]+)$/", strtolower($ip))) {
            return false;
        }

        // An IPV6 address needs at minimum two colons in it
        if (substr_count($ip, ":") < 2) {
            return false;
        }

        // If any of the "octets" are longer than 4 characters it's not valid
        $part = preg_split("/[:\/]/", $ip);
        foreach ($part as $i) {
            if (strlen($i) > 4) {
                return false;
            }
        }

        return true;
    }

    function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}
