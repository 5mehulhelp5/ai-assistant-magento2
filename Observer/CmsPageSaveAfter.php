<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Observer;

use Comerix\AiAssistant\Model\ChatApi\CmsPages;
use Comerix\AiAssistant\Service\Config;
use Magento\Cms\Model\Page;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CmsPageSaveAfter implements ObserverInterface
{
    /**
     * @param Config $config
     * @param CmsPages $cmsPages
     */
    public function __construct(
        private readonly Config $config,
        private readonly CmsPages $cmsPages
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        /** @var Page $page */
        $page = $observer->getEvent()->getPage();

        if (!$page->hasDataChanges()) {
            return;
        }

        $this->cmsPages->reindexCmsPage($page->getIdentifier(), 'save');
    }
}