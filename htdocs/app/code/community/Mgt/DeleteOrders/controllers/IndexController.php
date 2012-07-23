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

require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Mgt_DeleteOrders_IndexController extends Mage_Adminhtml_Sales_OrderController
{
    public function indexAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $deletedOrders = 0;
        if ($orderIds) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                $transactionContainer = Mage::getModel('core/resource_transaction');
                if ($order->getId()) {
                    $deletedOrders++;
                    // add invoices to delete
                    if ($order->hasInvoices()){
                      $invoices = Mage::getResourceModel('sales/order_invoice_collection')->setOrderFilter($orderId)->load();
                      if ($invoices) {
                          foreach ($invoices as $invoice){
                              $invoice = Mage::getModel('sales/order_invoice')->load($invoice->getId());
                              $transactionContainer->addObject($invoice);
                          }
                      }
                   }
                   
                   // add shipments to delete
                   if ($order->hasShipments()){
                       $shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($orderId)->load();
                       foreach ($shipments as $shipment){
                           $shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
                           $transactionContainer->addObject($shipment);
                       }
                   }
                   //delete
                   $transactionContainer->addObject($order)->delete();
                }
            }
        }
        
        if ($deletedOrders) {
            $this->_getSession()->addSuccess($this->__('%s order(s) was/were successfully deleted.', $deletedOrders));
        }
        $this->_redirect('adminhtml/sales_order/', array());
    }
}
