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

DROP TABLE IF EXISTS {$this->getTable('layaway/products')};
CREATE TABLE {$this->getTable('layaway/products')}(
    `layaway_id` INT( 11 ) NOT NULL AUTO_INCREMENT,
    `product_id` INT( 11 ) NOT NULL ,
    `layaway_status` TINYINT( 3 )  NOT NULL ,
    `layaway_fees` FLOAT( 11 )  NULL ,
    `layaway_first` FLOAT( 11 )  NULL ,
    `layaway_maxinstallments` INT( 11 )  NULL ,
    `layaway_period` INT( 11 )  NULL ,
    `layaway_period_frequency` INT( 11 )  NULL ,
    `config_layaway_period_frequency` SMALLINT( 1 )  NULL ,
    `config_layaway_period` SMALLINT( 1 )  NULL ,
    `config_layaway_status` SMALLINT( 1 )  NULL ,
    `config_layaway_fees` SMALLINT( 1 )  NULL ,
    `config_layaway_first` SMALLINT( 1 )  NULL ,
    `config_layaway_maxinstallments` SMALLINT( 1 )  NULL ,
    PRIMARY KEY ( `layaway_id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE {$this->getTable('sales/quote')}
        ADD `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD `layaway_order` INT( 11 ) NULL DEFAULT '0',
        ADD `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
    ALTER TABLE  {$this->getTable('sales/order')}
        ADD  `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD `layaway_order` INT( 11 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';
    ALTER TABLE {$this->getTable('sales/quote_item')}
      ADD `is_layaway` TINYINT( 3 ) NULL DEFAULT '0';
    ALTER TABLE  {$this->getTable('sales/quote_address')}
        ADD `is_layaway` TINYINT( 3 ) NULL DEFAULT '0',
        ADD  `layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0',
        ADD  `base_layaway_remaining` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.0';

   
");
$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');
//$statusLabelTable   = $installer->getTable('sales/order_status_label');

$data = array(
    array('status' => 'layaway_pending', 'label' => 'Layaway Pending'),
    array('status' => 'layaway_complete', 'label' => 'Layaway Complete')
);
$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);

$data = array(
    array('status' => 'layaway_pending', 'state' => 'layaway_pending', 'is_default' => 1),
    array('status' => 'layaway_complete', 'state' => 'layaway_complete', 'is_default' => 1)
);
$installer->getConnection()->insertArray($statusStateTable, array('status', 'state', 'is_default'), $data);
$installer->endSetup(); 