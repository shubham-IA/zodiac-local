<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class Failure extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /*$lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        return $this->resultPageFactory->create();*/
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $_checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
    $_quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
    
    $order = $_checkoutSession->getLastRealOrder();
    $quote = $_quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
    if ($quote->getId()) {
        $quote->setIsActive(1)->setReservedOrderId(null)->save();
        $_checkoutSession->replaceQuote($quote);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart');
        //$this->messageManager->addWarningMessage('Payment Failed.');
        return $resultRedirect;
    }
    }
}
