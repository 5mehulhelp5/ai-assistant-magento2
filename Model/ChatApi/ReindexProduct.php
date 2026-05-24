<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Model\ChatApi;

use Comerix\AiAssistant\Logger\Logger;
use Comerix\AiAssistant\Service\Config;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class ReindexProduct
{
    /**
     * @param Config $config
     * @param Curl $curl
     * @param Logger $logger
     * @param LoggerInterface $psrLogger
     */
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly Logger $logger,
        private readonly LoggerInterface $psrLogger
    ) {
    }

    /**
     * @param string $sku
     * @param string $action
     * @return bool
     */
    public function reindexProduct(string $sku, string $action): bool
    {
        return $this->request('/api/reindex-product', ['sku' => $sku, 'action' => $action]);
    }

    /**
     * @return bool
     */
    public function reindexProducts(): bool
    {
        return $this->request('/api/reindex-products');
    }

    /**
     * @param string $apiPath
     * @param array $params
     * @return bool
     */
    public function request(string $apiPath, array $params = []): bool
    {
        $serverUrl = $this->config->getChatServerUrl();
        $secret = $this->config->getReindexSecret();

        if (!$serverUrl || !$secret) {
            return false;
        }

        try {
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('x-reindex-secret', $secret);
            $this->curl->post(
                $serverUrl . $apiPath,
                $params ? (string) json_encode($params, JSON_THROW_ON_ERROR) : '{}'
            );

            $status = $this->curl->getStatus();
            if ($status < 200 || $status >= 300) {
                $this->logger->warning(
                    'Comerix_AiAssistant: API request failed.',
                    ['status' => $status, 'path' => $apiPath]
                );
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error(
                'Comerix_AiAssistant: API request exception.',
                ['path' => $apiPath]
            );
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
