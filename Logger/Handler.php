<?php

declare(strict_types=1);

namespace MageCloud\AiAssistant\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/magecloud_ai_assistant.log';
}
