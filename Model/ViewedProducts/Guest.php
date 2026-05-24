<?php
declare(strict_types=1);

namespace Comerix\AiAssistant\Model\ViewedProducts;

use Comerix\AiAssistant\Model\ViewedProducts as ViewedProductsModel;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class Guest
{
    private const MAX_VIEWED = 5;

    /**
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CatalogSession $catalogSession
     */
    public function __construct(
        private readonly CustomerSessionFactory $customerSessionFactory,
        private readonly CatalogSession $catalogSession,
    ) {}

    /**
     * @param array $data
     * @return void
     */
    public function saveData(array $data): void
    {
        $customerSession = $this->customerSessionFactory->create();
        $session = $customerSession->isLoggedIn()
            ? $customerSession
            : $this->catalogSession;

        $viewed = $session->getData(ViewedProductsModel::SESSION_KEY) ?? [];

        $viewed = array_values(
            array_filter($viewed, fn(array $item) => $item['sku'] !== $data['sku'])
        );
        array_unshift($viewed, $data);

        $viewed = array_slice($viewed, 0, self::MAX_VIEWED);
        $session->setData(ViewedProductsModel::SESSION_KEY, $viewed);
    }
}
