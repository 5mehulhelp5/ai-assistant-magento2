<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Block;

use Comerix\AiAssistant\Model\ViewedProducts;
use Comerix\AiAssistant\Service\Config;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Cms\Model\Page as CmsPage;
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
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ViewedProducts $viewedProducts
     * @param LoggerInterface $psrLogger
     * @param CmsPage $cmsPage
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Config $config,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly QuoteIdMaskResource $quoteIdMaskResource,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ViewedProducts $viewedProducts,
        private readonly LoggerInterface $psrLogger,
        private readonly CmsPage $cmsPage,
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
     * @return string
     */
    public function getChatServerUrl(): string
    {
        return $this->config->getChatServerUrl();
    }

    /**
     * @return string
     */
    public function getPpWidgetUrl(): string
    {
        return $this->config->getPpWidgetUrl();
    }

    /**
     * @return bool
     */
    public function isProductPageWidgetEnabled(): bool
    {
        return $this->config->isProductPageWidgetEnabled();
    }

    /**
     * @return bool
     */
    public function isCategoryWidgetEnabled(): bool
    {
        return $this->config->isCategoryWidgetEnabled();
    }

    /**
     * @return bool
     */
    public function isCartWidgetEnabled(): bool
    {
        return $this->config->isCartWidgetEnabled();
    }

    /**
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->customerSession->isLoggedIn()
            ? (int) $this->customerSession->getCustomerId()
            : null;
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
     * Returns viewed products as a JSON string for the chat init payload (guest session path).
     *
     * @return string
     */
    public function getViewedProductsJson(): string
    {
        $products = $this->viewedProducts->getViewedProducts();
        if (empty($products)) {
            return '[]';
        }
        return (string) json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    /**
     * @return string
     */
    public function getPageType(): string
    {
        return match ($this->getRequest()->getFullActionName()) {
            'catalog_product_view'         => 'product',
            'catalog_category_view'        => 'category',
            'checkout_cart_index'          => 'cart',
            'checkout_index_index'         => 'checkout',
            'cms_page_view', 'cms_index_index' => 'cms',
            default                        => 'other',
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

    /**
     * @return string
     */
    public function getProductName(): string
    {
        if ($this->getPageType() !== 'product') {
            return '';
        }

        try {
            $productId = (int) $this->getRequest()->getParam('id');
            return (string) $this->productRepository->getById($productId)->getName();
        } catch (NoSuchEntityException $e) {
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
            return '';
        }
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        if ($this->getPageType() !== 'category') {
            return '';
        }

        try {
            $categoryId = (int) $this->getRequest()->getParam('id');
            return (string) $this->categoryRepository->get($categoryId)->getName();
        } catch (NoSuchEntityException $e) {
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
            return '';
        }
    }

    /**
     * @return string
     */
    public function getPageUrl(): string
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        return match ($this->getPageType()) {
            'product'  => $this->getProductName(),
            'category' => $this->getCategoryName(),
            'cms'      => (string) $this->cmsPage->getTitle(),
            default    => '',
        };
    }

    /**
     * @return string
     */
    public function getCategoryUrl(): string
    {
        if ($this->getPageType() !== 'category') {
            return '';
        }

        try {
            $categoryId = (int) $this->getRequest()->getParam('id');
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($categoryId);
            return (string) $category->getUrl();
        } catch (NoSuchEntityException $e) {
            $this->psrLogger->error($e->getMessage(), ['exception' => $e]);
            return '';
        }
    }
}
