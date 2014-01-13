<?php

class Scandi_Iwoca_Block_Adminhtml_System_Config_IwocaBackend extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('iwoca/iwoca_backend.phtml');
        return $this;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

}