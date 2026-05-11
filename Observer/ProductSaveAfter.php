<?php

declare(strict_types=1);

namespace MageCloud\AiAssistant\Observer;

use MageCloud\AiAssistant\Logger\Logger;
use MageCloud\AiAssistant\Service\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @param Config $config
     * @param Curl $curl
     * @param Logger $logger
     * @param LoggerInterface $psrLogger
     */
    public function __construct(
        private Config $config,
        private Curl $curl,
        private Logger $logger,
        private LoggerInterface $psrLogger
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

        if (!$product->hasDataChanges()) {
            return;
        }

        $serverUrl = $this->config->getChatServerUrl();
        $secret = $this->config->getReindexSecret();

        if (!$serverUrl || !$secret) {
            return;
        }

        try {
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('Authorization', 'Bearer ' . $secret);
            $this->curl->post(
                $serverUrl . '/api/reindex-product',
                (string) json_encode(['sku' => $product->getSku(), 'secret' => $secret])
            );

            $status = $this->curl->getStatus();
            if ($status < 200 || $status >= 300) {
                $this->logger->warning(
                    'MageCloud_AiAssistant: reindex request failed.',
                    ['status' => $status, 'product_id' => $product->getId()]
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'MageCloud_AiAssistant: reindex request exception.',
                ['product_id' => $product->getId()]
            );
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}