<?php
class Scandi_Iwoca_Adminhtml_UninstallController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        Mage::getModel('core/config_data')
            ->load('iwoca/general/account', 'path')
            ->delete();

        Mage::getModel('core/config_data')
            ->load('iwoca/general/password', 'path')
            ->delete();

        Mage::getModel('core/config_data')
            ->load('iwoca/general/confirm', 'path')
            ->delete();

        function rrmdir($dir) {
            foreach(glob($dir . '/*') as $file) {
                if(is_dir($file)) {
                    rrmdir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($dir);
        }

        unlink('skin/adminhtml/default/default/scandi_iwoca.css');
        unlink('app/locale/en_US/template/email/iwoca_email.html');
        unlink('app/design/adminhtml/default/default/template/iwoca/iwoca_backend.phtml');
        unlink('app/design/adminhtml/default/default/layout/scandi_iwoca.xml');
        unlink('iwoca_ext_confirm.php');
        rrmdir('js/scandi_iwoca/');
        rrmdir('app/code/local/Scandi/Iwoca/');
        rrmdir('var/iwoca/');
        unlink('app/etc/modules/Scandi_Iwoca.xml');

        $this->getResponse()->setBody('<p class="ext-del">The extension was successfully uninstalled. To reinstall the extension follow the steps in the accounts section of your iwoca profile.</p>');
    }
}