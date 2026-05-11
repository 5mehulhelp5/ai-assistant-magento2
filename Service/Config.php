<?php

declare(strict_types=1);

namespace MageCloud\AiAssistant\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'magecloud_ai_assistant/general/enabled';
    private const XML_PATH_WIDGET_URL = 'magecloud_ai_assistant/general/widget_url';
    private const XML_PATH_CHAT_SERVER_URL = 'magecloud_ai_assistant/general/chat_server_url';
    private const XML_PATH_REINDEX_SECRET = 'magecloud_ai_assistant/general/reindex_secret';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWidgetUrl(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_WIDGET_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getChatServerUrl(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CHAT_SERVER_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getReindexSecret(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_REINDEX_SECRET,
            ScopeInterface::SCOPE_DEFAULT
        );
    }
}