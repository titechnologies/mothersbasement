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

class FME_Layaway_Adminhtml_LayawayController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->_title($this->__('Layaway'))->_title($this->__('Orders'));
		$this->loadLayout()
			->_setActiveMenu('fme_extensions')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Layaway Manager'), Mage::helper('adminhtml')->__('Layaway Manager'));
		$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	
	
	public function viewAction() {
		
		
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('sales/order')->load($id);
		
		if ($model->getEntityId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			Mage::register('layaway_data', $model);
			Mage::register('current_order', $model);
			Mage::register('sales_order', $model);
			
			$this->loadLayout();
			$this->_setActiveMenu('fme_extensions');
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
			$this->_title($this->__('Layaway'))->_title($this->__('Orders'))->_title($this->__('#') . $model->getIncrementId());
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Layaway Manager'), Mage::helper('adminhtml')->__('Layaway Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Layaway Layaway'), Mage::helper('adminhtml')->__('Layaway Layaway'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('layaway')->__('Layaway Order does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	//public function newAction() {
	//	$this->_forward('view');
	//}
 
	public function layawayAction() {
		$orderdetail = $this->getRequest()->getPost();
		
		if($orderdetail && $orderdetail['payKey']!=''){
		    $return = Mage::getModel('layaway/layaway')->requestLayaway($orderdetail);
		    //print_r($return);exit;
		    if(strtolower($return['responseEnvelope.ack']) != "success"){
			$result['success'] = false;
			$result['error'] = true;
			$result['messages'] = Mage::helper('layaway')->__($return['error(0).message']);
			$this->getResponse()->setBody(Zend_Json::encode($result));
			return;
		    }else{
			$layawaymessages = Mage::helper('layaway')->getLayawayResponseMessage();
			$result['success'] = true;
			$result['error'] = false;
			$result['messages'] = Mage::helper('layaway')->__($layawaymessages[$return['layawayInfoList.layawayInfo(0).layawayStatus']]);
			$this->getResponse()->setBody(Zend_Json::encode($result));
			return;
		    }
		    
		}
		$result['success'] = false;
		$result['error'] = true;
		$result['messages'] = Mage::helper('layaway')->__('This order wasn\'t placed using Adaptive Paypal');
		$this->getResponse()->setBody(Zend_Json::encode($result));
		return;
	}
 
  
    public function exportCsvAction()
    {
        $fileName   = 'layaway.csv';
        $content    = $this->getLayout()->createBlock('layaway/adminhtml_layaway_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'layaway.xml';
        $content    = $this->getLayout()->createBlock('layaway/adminhtml_layaway_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
	
	
   
    public function gridAction()
	{
		 
	    $this->getResponse()->setBody(
            $this->getLayout()->createBlock('layaway/adminhtml_layaway_grid')->toHtml()
        );
	
	}

 
}