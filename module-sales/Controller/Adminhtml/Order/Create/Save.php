<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\PaymentException;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Create implements HttpPostActionInterface
{
    /**
     * Saving quote and create order
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $path = 'sales/*/';
        $pathParams = [];

        try {
            // check if the creation of a new customer is allowed
            if (!$this->_authorization->isAllowed('Magento_Customer::manage')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
            ) {
                return $this->resultForwardFactory->create()->forward('denied');
            }
            $this->_getOrderCreateModel()->getQuote()->setCustomerId($this->_getSession()->getCustomerId());
            $this->_processActionData('save');
            $paymentData = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                $paymentData['checks'] = [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_INTERNAL,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();
            
            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccessMessage(__('You created the order.'));
            if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
                $pathParams = ['order_id' => $order->getId()];
                $path = 'sales/order/view';
            } else {
                $path = 'sales/order/index';
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $session = $objectManager->create('Magento\Checkout\Model\Session');
			$order1 = $objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
			//echo '<pre>';print_r($order1->getData());exit;
			$order1->setCustomerEmail($order->getCustomerEmail());
			$order1->setCanSendNewEmailFlag(true);
			$order1->save(); 
			//$session->setForceOrderMailSentOnSuccess(true);			
			$orderSender = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\OrderSender');
			$orderSender->send($order1);
        } catch (PaymentException $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // customer can be created before place order flow is completed and should be stored in current session
            $this->_getSession()->setCustomerId((int)$this->_getSession()->getQuote()->getCustomerId());
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Order saving error: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath($path, $pathParams);
    }
}
