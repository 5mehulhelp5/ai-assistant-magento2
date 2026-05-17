<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Controller\Adminhtml\Reindex;

use Comerix\AiAssistant\Service\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class Products extends Action
{
    public const ADMIN_RESOURCE = 'Comerix_AiAssistant::config';

    /**
     * @param Context $context
     * @param Config $config
     * @param Curl $curl
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly JsonFactory $jsonFactory,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        $serverUrl = $this->config->getChatServerUrl();
        $secret = $this->config->getReindexSecret();

        if (!$serverUrl || !$secret) {
            return $result->setData(['success' => false, 'message' => 'Chat Server URL or Reindex Secret is not configured.']);
        }

        try {
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('x-reindex-secret', $secret);
            $this->curl->post(
                $serverUrl . '/api/reindex-products',
                '{}'
            );

            $status = $this->curl->getStatus();

            if ($status < 200 || $status >= 300) {
                $this->logger->warning('Comerix_AiAssistant: reindex-products request failed.', ['status' => $status]);
                return $result->setData(['success' => false, 'message' => sprintf('Request failed with status %d.', $status)]);
            }

            return $result->setData(['success' => true, 'message' => 'Reindex completed successfully.']);
        } catch (\Exception $e) {
            $this->logger->error('Comerix_AiAssistant: reindex-products exception: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => 'An error occurred. Please check the logs.']);
        }
    }
}
