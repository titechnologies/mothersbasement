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

class FME_Layaway_Model_Layaway extends Mage_Core_Model_Abstract
{
 public function _construct()
    {
        parent::_construct();
        $this->_init('layaway/layaway');
    }
	
   
    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getLayawayRelatedProducts($layawayId)
    {
	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
	$select = $read->select()
            ->from(Mage::helper('layaway')->getFMTable('products'))
            ->where('attribute_id = ?', $layawayId);

        if ($datas = $read->fetchAll($select)) {
            $locationArray = array();
            foreach ($datas as $row) {
                $locationArray[] = $row['products_id'];
            }
	  
         return $locationArray;
        }
	return;

    }
	
} 