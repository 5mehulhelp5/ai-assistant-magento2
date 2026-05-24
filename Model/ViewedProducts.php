<?php
declare(strict_types=1);

namespace Comerix\AiAssistant\Model;

use Magento\Customer\Model\SessionFactory;
use Magento\Catalog\Model\Session as CatalogSession;
use Comerix\AiAssistant\Model\ViewedProducts\Customer as ViewedProductsCustomer;

class ViewedProducts
{
    public const SESSION_KEY = 'aiassistant_viewed_products';

    /**
     * @param SessionFactory $customerSessionFactory
     * @param CatalogSession $catalogSession
     * @param ViewedProductsCustomer $viewedProductsCustomer
     */
    public function __construct(
        private readonly SessionFactory $customerSessionFactory,
        private readonly CatalogSession $catalogSession,
        private readonly ViewedProductsCustomer $viewedProductsCustomer
    ) {}

    /**
     * @param int|null $customerId Explicit customer ID for API callers; null falls back to session.
     * @return array
     */
    public function getViewedProducts(?int $customerId = null): array
    {
        if ($customerId !== null) {
            return $this->viewedProductsCustomer->getList($customerId);
        }

        $customerSession = $this->customerSessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerViewedProducts = $this->viewedProductsCustomer->getList(
                (int) $customerSession->getCustomerId()
            );

            if (!empty($customerViewedProducts)) {
                return $customerViewedProducts;
            }
        }

        return $customerSession->isLoggedIn()
            ? ($customerSession->getData(self::SESSION_KEY) ?? [])
            : ($this->catalogSession->getData(self::SESSION_KEY) ?? []);
    }

    /**
     * Returns a formatted block ready for injection into an LLM system prompt.
     *
     * @return string
     */
    public function buildLlmContext(): string
    {
        $products = $this->getViewedProducts();

        if (empty($products)) {
            return '';
        }

        $lines = array_map(function (array $p): string {
            $price = number_format($p['price'], 2);
            return "- {$p['name']} (SKU: {$p['sku']}, Price: \${$price})";
        }, $products);

        return "Recently viewed products (most recent first):\n" . implode("\n", $lines);
    }
}
