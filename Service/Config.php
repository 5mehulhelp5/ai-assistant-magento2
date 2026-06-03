<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Admin config path
     */
    private const XML_PATH_ENABLED = 'comerix_ai_assistant/general/enabled';
    private const XML_PATH_WIDGET_URL = 'comerix_ai_assistant/general/widget_url';
    private const XML_PATH_CHAT_SERVER_URL = 'comerix_ai_assistant/general/chat_server_url';
    private const XML_PATH_REINDEX_SECRET = 'comerix_ai_assistant/general/reindex_secret';
    private const XML_PATH_PP_WIDGET_ENABLED = 'comerix_ai_assistant/widget_additional_config/enable_pp_widget';
    private const XML_PATH_PP_WIDGET_URL = 'comerix_ai_assistant/widget_additional_config/pp_widget_url';
    private const XML_PATH_CATEGORY_WIDGET_ENABLED = 'comerix_ai_assistant/widget_additional_config/enable_category_widget';
    private const XML_PATH_CART_WIDGET_ENABLED = 'comerix_ai_assistant/widget_additional_config/enable_cart_widget';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
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
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_REINDEX_SECRET
        );

        return $value ? $this->encryptor->decrypt($value) : '';
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getPpWidgetUrl(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PP_WIDGET_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isProductPageWidgetEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PP_WIDGET_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCategoryWidgetEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_WIDGET_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCartWidgetEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CART_WIDGET_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
