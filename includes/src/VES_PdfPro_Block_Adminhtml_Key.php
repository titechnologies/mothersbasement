<?php
class VES_PdfPro_Block_Adminhtml_Key extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_key';
    $this->_blockGroup = 'pdfpro';
    $this->_headerText = Mage::helper('pdfpro')->__('API Key Manager');
    parent::__construct();
  }
}