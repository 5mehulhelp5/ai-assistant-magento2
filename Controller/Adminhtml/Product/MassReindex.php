<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Controller\Adminhtml\Product;

use Comerix\AiAssistant\Model\ChatApi\ReindexProduct;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassReindex extends Action
{
    public const ADMIN_RESOURCE = 'Comerix_AiAssistant::config';

    /**
     * @param Context $context
     * @param ReindexProduct $reindexProduct
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        private readonly ReindexProduct $reindexProduct,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('catalog/product/index');

        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $successCount = 0;
        $failCount = 0;

        foreach ($collection as $product) {
            if ($this->reindexProduct->reindexProduct($product->getSku(), 'save')) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($successCount > 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 product(s) successfully sent to AiAssistant for reindex.', $successCount)
            );
        }

        if ($failCount > 0) {
            $this->messageManager->addErrorMessage(
                __('%1 product(s) failed to send to AiAssistant. Please check the logs.', $failCount)
            );
        }

        return $redirect;
    }
}