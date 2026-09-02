<?php

namespace Modules\Main\Consumer;

use Modules\Main\Model\ChatMessageModel;
use Modules\Main\Model\CorpModel;
use Modules\Main\Service\ChatSessionPullService;
use Throwable;

readonly class DownloadStructuredMessageMediasConsumer
{
    public function __construct(
        private CorpModel $corp,
        private ChatMessageModel $message,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        ChatSessionPullService::handleStructuredMessageMedias($this->corp, $this->message);
    }
}
