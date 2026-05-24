<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Observer;

use Comerix\AiAssistant\Model\ChatApi\ReindexProduct;
use Comerix\AiAssistant\Service\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductDeleteAfter implements ObserverInterface
{
    /**
     * @param Config $config
     * @param ReindexProduct $reindexProduct
     */
    public function __construct(
        private readonly Config $config,
        private readonly ReindexProduct $reindexProduct
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        $this->reindexProduct->reindexProduct($product->getSku(), 'delete');
    }
}