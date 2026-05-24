<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Controller\Adminhtml\Reindex;

use Comerix\AiAssistant\Model\ChatApi\ReindexProduct;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Products extends Action
{
    public const ADMIN_RESOURCE = 'Comerix_AiAssistant::config';

    /**
     * @param Context $context
     * @param ReindexProduct $reindexProduct
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        private readonly ReindexProduct $reindexProduct,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        if ($this->reindexProduct->reindexProducts()) {
            return $result->setData(['success' => true, 'message' => 'Reindex completed successfully.']);
        }

        return $result->setData(['success' => false, 'message' => 'Request failed. Please check the logs.']);
    }
}
