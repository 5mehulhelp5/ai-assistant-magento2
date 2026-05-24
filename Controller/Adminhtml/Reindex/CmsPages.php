<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Controller\Adminhtml\Reindex;

use Comerix\AiAssistant\Model\ChatApi\CmsPages as CmsPagesModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class CmsPages extends Action
{
    public const ADMIN_RESOURCE = 'Comerix_AiAssistant::config';

    /**
     * @param Context $context
     * @param CmsPagesModel $cmsPages
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        private readonly CmsPagesModel $cmsPages,
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
        ['total' => $total, 'sent' => $sent] = $this->cmsPages->reindexAllCmsPages();

        if ($total === 0) {
            return $result->setData(['success' => false, 'message' => 'No CMS pages found in the catalog.']);
        }

        if ($sent === 0) {
            return $result->setData(['success' => false, 'message' => sprintf(
                'Found %d page(s) but all requests failed. Check Chat Server URL, Reindex Secret, and var/log/.',
                $total
            )]);
        }

        return $result->setData(['success' => true, 'message' => sprintf('Reindexed %d of %d CMS page(s) successfully.', $sent, $total)]);
    }
}