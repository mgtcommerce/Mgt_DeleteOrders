<?php
/**
 * MGT-Commerce GmbH
 * http://www.mgt-commerce.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mgt-commerce.com so we can send you a copy immediately.
 *
 * @category    Mgt
 * @package     Mgt_DeleteOrders
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2012 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mgt_DeleteOrders_Model_Observer
{
    const SALES_ORDER_GRID_NAME = 'sales_order_grid';
    
    public function addOptionToSelect($observer)
    {
        if (self::SALES_ORDER_GRID_NAME == $observer->getEvent()->getBlock()->getId()) {
            $massBlock = $observer->getEvent()->getBlock()->getMassactionBlock();
            if ($massBlock) {
                $massBlock->addItem('mgt_delete_orders', array(
                    'label'=> Mage::helper('core')->__('Delete'),
                    'url'  => Mage::getUrl('mgt_delete_orders', array('_secure'=>true)),
                    'confirm' => Mage::helper('core')->__('Are you sure to delete the selected orders?'),
                ));
            }
        }
    }
    
    public function deleteOrderFromGrid($observer)
    {
        // This is actually not needed for databases with working foreign keys but some databases are corrupt :(
        $order = $observer->getOrder();
        if ($order->getId()) {
            $coreResource = Mage::getSingleton('core/resource');
            $writeConnection = $coreResource->getConnection('core_write');
            $salesOrderGridTable = $coreResource->getTableName('sales_flat_order_grid');
            $query = sprintf('Delete from %s where entity_id = %s', $salesOrderGridTable, (int)$order->getId());
            $writeConnection->raw_query($query);
        }
    }
}
