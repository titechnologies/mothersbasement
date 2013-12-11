<?php
class TM_SoldTogether_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('soldtogether/order','relation_id');
    }

    public function getProductIds($product_id, $lim)
    {
        $connection = $this->_getReadAdapter();

        $select = $this->_getReadAdapter()->select()
               ->from(array('so' => $this->getMainTable()), 'related_product_id')
               ->where('product_id = ?', $product_id)
               ->order('weight desc')
               ->limit($lim);

        if(!$connection->fetchCol($select)) {
            if (Mage::getStoreConfig('soldtogether/general/random')) {
                return $this->getChildrenCategoryProducts($product_id, $lim);
            }
            return 0;
        }

        return $connection->fetchCol($select);
    }

    public function arrayRandom($arr, $num = 1) {
        shuffle($arr);

        $r = array();
        for ($i = 0; $i < $num; $i++) {
            $r[] = $arr[$i];
        }
        return $num == 1 ? $r[0] : $r;
    }

    public function getChildrenCategoryProducts($productId, $limit)
    {
        $product = Mage::getModel('catalog/product');
        $product->load($productId);
        $categoryId = $product->getCategoryId();
        $category = Mage::getModel('catalog/category');
        $category->load($categoryId);
        $prdIds = array();
        $productCollection = $category->getProductCollection();
        foreach ($productCollection as $product) {
            if ($product->getId() != $productId) {
                $prdIds[] = $product->getId();
            }
        }
        if (count($prdIds) > 0) {
            return $this->arrayRandom($prdIds, $limit);
        } else {
            $collection = Mage::getModel('catalog/product')
                 ->getCollection();
            $collection->addAttributeToFilter('entity_id', array('nin'=>array($productId)));
            $productIds = $collection->getAllIds();
            return $this->arrayRandom($productIds, $limit);
        }

    }

    public function getXZ($count = 1, $step = 0)
    {
        $connection = $this->_getReadAdapter();

        $orderSelect = $connection->select()
            ->from(array('so' => $this->getTable('sales/order')),'entity_id')
            ->order('entity_id')
            ->limit($count, $count * $step);

        $orderIds = $connection->fetchCol($orderSelect);

        $select = $this->_getReadAdapter()->select()
            ->from(array('soi' => $this->getTable('sales/order_item')),
                array('order_id', 'product_id', 'parent_item_id'))
            ->joinInner(array('cp' => $this->getTable('catalog/product')),
                'cp.entity_id = soi.product_id',
                array())
            ->where('order_id IN (?)', $orderIds)
            ->order(array('order_id', 'product_id'));

        $result = array();
        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            if (!isset($result[$row['order_id']])) {
                $result[$row['order_id']] = array();
            }
            if (!isset($result[$row['order_id']][$row['parent_item_id']])) {
                $result[$row['order_id']][$row['parent_item_id']] = array();
            }
            $result[$row['order_id']][$row['parent_item_id']][] = $row['product_id'];
        }
        return $result;
    }

    public function getOrderObserverData($orderId)
    {
        $connection = $this->_getReadAdapter();
        $orderIds = array();
        $orderIds[] = $orderId;

        $select = $this->_getReadAdapter()->select()
            ->from(array('soi' => $this->getTable('sales/order_item')),
                array('order_id', 'product_id', 'parent_item_id'))
            ->joinInner(array('cp' => $this->getTable('catalog/product')),
                'cp.entity_id = soi.product_id',
                array())
            ->where('order_id IN (?)', $orderIds)
            ->order(array('order_id', 'product_id'));
        $result = array();
        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            if (!isset($row['parent_item_id'])) {
                $result[] = $row['product_id'];
            }
        }
        return $result;
    }
    
    

    public function deleteData($where)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
    }

    public function saveData($data)
    {
        $connection = $this->_getReadAdapter();

        $select = $connection->select()
            ->from(array('so' => $this->getMainTable()))
            ->where('product_id = ?', $data['product_id'])
            ->where('related_product_id = ?', $data['related_product_id']);

        if (!($row = $connection->fetchRow($select))) {
            $row['weight'] = 0;
        } elseif ($row['is_admin']
            && (!isset($data['override_admin']) || !$data['override_admin'])) {

            return;
        } else {
            $data['relation_id'] = $row['relation_id'];
            if (isset($data['weight']) && ($row['weight'] != $data['weight'])) {
                $data['is_admin'] = 1;
            } else {
                $data['is_admin'] = $row['is_admin'];
            }
        }

        if (!isset($data['weight']) || !$data['weight']) {
            $data['weight'] = $row['weight'] + 1;
        }

        $order = Mage::getModel('soldtogether/order');
        $order->addData($data);

        $this->save($order);
    }

    public function clearTable()
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), 'is_admin = 0');
        $this->_getWriteAdapter()->commit();
        $this->autoincremetCustomerTable($this->getMaxAdminId());
    }
    
    public function autoincremetCustomerTable($maxId)
    {
        if ($maxId == null) {
            $id = 1;
        } else {
            $id = $maxId + 1;
        }
        
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->query("ALTER TABLE " . $this->getTable('soldtogether/order') . " AUTO_INCREMENT = " . $id);
        $write->commit();
        
        return $this;
    }

    public function getMaxAdminId()
    {
        $connection = $this->_getReadAdapter();

        $select = $connection->select()
            ->from(array('so' => $this->getMainTable()),
                array('MAX(relation_id)'))
            ->where('is_admin = ?', 1);

        $result = $connection->fetchRow($select);

        return $result['MAX(relation_id)'];
    }

    public function getRelated($productId)
    {
        $connection = $this->_getReadAdapter();

        $select = $connection->select()
            ->from(array('so' => $this->getMainTable()),
                array('related_product_id', 'weight'))
            ->where('product_id = ?', $productId);

        return $connection->fetchAll($select);
    }
    
    public function addOrderProductData($order)
    {
        $orderId = $order->getId();
        $res = $this->getOrderObserverData($orderId);
        
        $result = array();
        for ($i=0;$i<count($res);$i++) {
            for ($j=0;$j<count($res);$j++) {
                if ($res[$i] == $res[$j]) {
                    continue;
                }
                if ($this->productsExist($res[$i], $res[$j])) {
                    $result['product_id'] = $res[$i];
                    $result['related_product_id'] = $res[$j];
                    $this->saveData($result);
                }
                $result = array();
            }
        }
    }
    
    public function productsExist($product1, $product2)
    {
        $model1 = Mage::getModel('catalog/product');
        $model2 = Mage::getModel('catalog/product');
        $model1->load($product1);
        $model2->load($product2);
        if ($model1->getId() && $model2->getId()) {
            return true;
        }
        return false;
    }
}