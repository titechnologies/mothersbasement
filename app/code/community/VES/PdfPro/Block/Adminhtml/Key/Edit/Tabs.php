<?php

class VES_PdfPro_Block_Adminhtml_Key_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('vendor_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('pdfpro')->__('API Key Information'));
  }

  protected function _beforeToHtml()
  {
	  	$this->addTab('form', array(
	  			'label'     => Mage::helper('pdfpro')->__('API Key Information'),
	  			'title'     => Mage::helper('pdfpro')->__('API Key Information'),
	  			'content'   => $this->getLayout()->createBlock('pdfpro/adminhtml_key_edit_tab_form')->toHtml(),
	  	));
      	return parent::_beforeToHtml();
  }
}