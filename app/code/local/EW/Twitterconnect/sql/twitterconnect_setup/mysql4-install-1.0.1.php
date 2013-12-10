<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('customer', 'ew_twitter_screen', array(
		'group' 		=> 'Default',
		'type' 			=> 'text',
		'input' 		=> 'text',
	    'label' 		=> 'Twitter Screen Name',
	    'required' 		=> false,
	    'default' 		=> '',
		'visible' 		=> true,
		'user_defined' 	=> 0,
		'position' 		=> 100,
		'sort_order'	=> 100
));


$installer->endSetup(); 