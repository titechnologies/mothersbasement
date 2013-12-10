<?php

$installer = $this;
try{

$vers = Mage::getVersion();
$model = new FBShop_Platform_Model_Platform();
$host=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$pv=$model->version;
$url=$model->host."/statistics/magento/after_install?v=".$vers."&p=".$host."&pv=".$pv;
$notty = Mage::getModel('adminnotification/inbox');

   $notty->setTitle("Shopidoo extension installed sucessfully! Click on `Facebook Shop` tab to sign up and launch your shop on Facebook.");
   $notty->setDescription('Welcome to Shopidoo - your shop on Facebook! On `Facebook Shop` tab in your admin panel you will find interfaces to operate your shop on Facebook. Increase virality of your shop and enhance it with social features!
                         Your Shopidoo Team');
   $notty->setSeverity(4);
   $notty->setUrl($model->host);
   $notty->save();

//echo $url;
//echo $host.$urlTab;
$var= new Mage_HTTP_Client_Curl();
       $var->post($url);

   }  catch (Exception $e){
//       echo $e->getMessage();
//       die();
   }
$installer->startSetup();
// 
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('platform')};
CREATE TABLE {$this->getTable('platform')} (
  `platform_id` int(11) unsigned NOT NULL auto_increment,
  `version` varchar(255) NOT NULL default '',
  `host` varchar(255) NOT NULL default '',
  `shop` varchar(255) NOT NULL default '',
  `user` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`platform_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");

         

$installer->endSetup(); 