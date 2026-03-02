<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Chat Entry resource model – maps to bs23_chat_entry table.
 */
class ChatEntry extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('bs23_chat_entry', 'entry_id');
    }
}
