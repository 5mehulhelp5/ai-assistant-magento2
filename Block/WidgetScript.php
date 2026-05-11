<?php

declare(strict_types=1);

namespace MageCloud\AiAssistant\Block;

use MageCloud\AiAssistant\Service\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Psr\Log\LoggerInterface;

class WidgetScript extends Template
{
    /**
     * @param Context $context
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $psrLogger
     * @param array $data
     */
    public function __construct(
        Context $context,
        private Config $config,
        private CheckoutSession $checkoutSession,
        private CustomerSession $customerSession,
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        private QuoteIdMaskResource $quoteIdMaskResource,
        private ProductRepositoryInterface $productRepository,
        private LoggerInterface $psrLogger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @return string
     */
    public function getWidgetUrl(): string
    {
        return $this->config->getWidgetUrl();
    }

    /**
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Return quote mask Id
     * @return string
     */
    public function getQuoteMask(): string
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            if (!$quote->getId()) {
                return '';
            }

            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $this->quoteIdMaskResource->load($quoteIdMask, $quote->getId(), 'quote_id');

            if (!$quoteIdMask->getMaskedId()) {
                $quoteIdMask->setQuoteId($quote->getId());
                $this->quoteIdMaskResource->save($quoteIdMask);
            }

            return (string) $quoteIdMask->getMaskedId();
        } catch (\Exception $e) {
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
            return '';
        }
    }

    /**
     * @return string
     */
    public function getPageType(): string
    {
        return match ($this->getRequest()->getFullActionName()) {
            'catalog_product_view'  => 'product',
            'catalog_category_view' => 'category',
            default                 => 'other',
        };
    }

    /**
     * @return string
     */
    public function getProductSku(): string
    {
        if ($this->getPageType() !== 'product') {
            return '';
        }

        try {
            $productId = (int) $this->getRequest()->getParam('id');
            return $this->productRepository->getById($productId)->getSku();
        } catch (NoSuchEntityException $e) {
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
            return '';
        }
    }
}
