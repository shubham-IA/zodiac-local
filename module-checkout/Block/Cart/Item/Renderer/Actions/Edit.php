<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer\Actions;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends Generic
{
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
		$extraUrl = '';
		//echo '<pre>';
		//return($this->getItem()->getProductOptions());exit;
		if($sizeOption = $this->getItem()->getOptionByCode('simple_product')) {
			if($sizeOption->getProduct()!=NULL){
		$c_productId = $sizeOption->getProduct()->getId();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product_data = $objectManager->get('Magento\Catalog\Model\Product')->load($c_productId);
		$size = $product_data->getSize();
		$fit = $product_data->getFit();
		
		
		if ($size!='') {
			$extraUrl.='&153='.$size;
		}
		if ($fit!='') {
			$extraUrl.='&200='.$fit;
		}
			}
		}
		/*
		if ($size!='') {
			$extraUrl.=',153='.$size;
		}
		
		*/
		
        return $this->getUrl(
            'checkout/cart/configure',
            [
                'id' => $this->getItem()->getId(),
                'product_id' => $this->getItem()->getProduct()->getId()
				
            ]
        ).'#'.ltrim($extraUrl,'&');
		
    }
}
