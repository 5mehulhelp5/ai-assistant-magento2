<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Model\ChatApi;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class CmsPages
{
    /**
     * @param ReindexProduct $reindexProduct
     * @param CollectionFactory $pageCollectionFactory
     */
    public function __construct(
        private readonly ReindexProduct $reindexProduct,
        private readonly CollectionFactory $pageCollectionFactory
    ) {
    }

    /**
     * @param string $identifier
     * @param string $action
     * @return bool
     */
    public function reindexCmsPage(string $identifier, string $action): bool
    {
        return $this->reindexProduct->request('/api/reindex-cms-page', ['identifier' => $identifier, 'action' => $action]);
    }

    /**
     * @return array{total: int, sent: int}
     */
    public function reindexAllCmsPages(): array
    {
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToSelect('identifier');

        $total = $collection->getSize();
        $sent = 0;

        foreach ($collection as $page) {
            if ($this->reindexCmsPage($page->getIdentifier(), 'save')) {
                $sent++;
            }
        }

        return ['total' => $total, 'sent' => $sent];
    }
}