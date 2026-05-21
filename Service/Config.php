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
    private const XML_PATH_WIDGET_COLOR = 'comerix_ai_assistant/chat_widget/widget_color';
    private const XML_PATH_TITLE = 'comerix_ai_assistant/chat_widget/title';
    private const XML_PATH_SUBTITLE = 'comerix_ai_assistant/chat_widget/subtitle';
    private const XML_PATH_WELCOME_MESSAGE = 'comerix_ai_assistant/chat_widget/welcome_message';
    private const XML_PATH_POSITION = 'comerix_ai_assistant/chat_widget/position';
    private const XML_PATH_PP_WIDGET_ENABLED = 'comerix_ai_assistant/pp_widget/enable_pp_widget';
    private const XML_PATH_PP_WIDGET_URL = 'comerix_ai_assistant/pp_widget/pp_widget_url';

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
    public function getWidgetColor(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_WIDGET_COLOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getTitle(?int $storeId = null): string
    {
        return (string) ($this->scopeConfig->getValue(
            self::XML_PATH_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'Chat with us');
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSubtitle(?int $storeId = null): string
    {
        return (string) ($this->scopeConfig->getValue(
            self::XML_PATH_SUBTITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'Your personal AI assistant is here and ready to go');
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWelcomeMessage(?int $storeId = null): string
    {
        return (string) ($this->scopeConfig->getValue(
            self::XML_PATH_WELCOME_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'Hi! How can we help you today?');
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
     * @return string
     */
    public function getPosition(?int $storeId = null): string
    {
        return (string) ($this->scopeConfig->getValue(
            self::XML_PATH_POSITION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'right');
    }
}
