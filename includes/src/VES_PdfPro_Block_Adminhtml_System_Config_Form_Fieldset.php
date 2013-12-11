<?php
/**
 * Config form fieldset renderer
 *
 * @category   VES
 * @package    VES_PdfPro
 * @author     Easy PDF Invoice Team <support@easypdfinvoice.com>
 */
class VES_PdfPro_Block_Adminhtml_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $html = '
        <div style="display: block;">
        <table class="form-list">
	        <tr>
	        	<td class="label">Extension Version</td><td><strong style="color: #1f5e00;">'.PdfPro::getVersion().'</strong></td>
	        </tr>
        </table>
        </div>';
    	return $html.$element->getComment();
    }
}