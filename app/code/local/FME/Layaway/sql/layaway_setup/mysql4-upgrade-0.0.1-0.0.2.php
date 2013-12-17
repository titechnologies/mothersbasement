<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\   FME Layaway extension  \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   FME                            ///////
 \\\\\\\                      * @package    FME_Layaway                    \\\\\\\
 ///////    * @author     Malik Tahir Mehmood <malik.tahir786@gmail.com>   ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\* @copyright  Copyright 2010 © free-magentoextensions.com All right reserved\\\
 /////////////////////////////////////////////////////////////////////////////////
 */


$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE  {$this->getTable('sales/order_item')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
    ALTER TABLE {$this->getTable('sales/quote_item')}
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
    ALTER TABLE  {$this->getTable('sales/order')}
        ADD  `layaway_info` TEXT NULL;
    ALTER TABLE  {$this->getTable('sales/quote')}
        ADD  `layaway_info` TEXT NULL;
    ALTER TABLE  {$this->getTable('sales/invoice')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_order` INT( 11 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_info` TEXT NULL;
    ALTER TABLE  {$this->getTable('sales/invoice_item')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
        
    ALTER TABLE  {$this->getTable('sales/creditmemo')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_order` INT( 11 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_info` TEXT NULL;
    ALTER TABLE  {$this->getTable('sales/creditmemo_item')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
    ALTER TABLE  {$this->getTable('sales/shipment')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_order` INT( 11 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_info` TEXT NULL;
    ALTER TABLE  {$this->getTable('sales/shipment_item')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
   
");

$installer->endSetup(); 