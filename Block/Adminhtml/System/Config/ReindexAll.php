<?php

declare(strict_types=1);

namespace Comerix\AiAssistant\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ReindexAll extends Field
{
    /**
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $url = $this->getUrl('comerix_ai_assistant/reindex/products');
        $buttonId = $element->getHtmlId() . '_button';
        $statusId = $element->getHtmlId() . '_status';

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $enabledCount = $collection->getSize();

        return sprintf(
            '<button type="button" id="%s" class="action-default scalable" onclick="%s">'
            . '<span>Run Reindex</span></button>'
            . '<span id="%s" style="margin-left:10px;vertical-align:middle;"></span>'
            . '<script>
                function reindexAll_%s() {
                    var btn = document.getElementById("%s");
                    var status = document.getElementById("%s");
                    btn.disabled = true;
                    status.innerHTML = "Running...";
                    fetch("%s", {method:"POST", headers:{"X-Requested-With":"XMLHttpRequest","Content-Type":"application/x-www-form-urlencoded"},body:"form_key="+window.FORM_KEY})
                        .then(function(r){ return r.json(); })
                        .then(function(data){
                            status.innerHTML = data.message;
                            status.style.color = data.success ? "green" : "red";
                            btn.disabled = false;
                        })
                        .catch(function(){
                            status.innerHTML = "Request failed.";
                            status.style.color = "red";
                            btn.disabled = false;
                        });
                }
            </script>'
            . '<p style="margin-top:6px;color:#666;">%d products enabled in catalog</p>',
            $buttonId,
            'reindexAll_' . $buttonId . '()',
            $statusId,
            $buttonId,
            $buttonId,
            $statusId,
            $url,
            $enabledCount
        );
    }

    /**
     * Remove label/scope columns — render the button cell full-width.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
}