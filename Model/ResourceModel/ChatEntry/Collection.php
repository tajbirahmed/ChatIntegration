<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\ResourceModel\ChatEntry;

use BS23\ChatIntegration\Model\ChatEntry;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry as ChatEntryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Chat Entry collection.
 */
class Collection extends AbstractCollection
{
    /** @var string Primary key field */
    protected $_idFieldName = 'entry_id';

    protected $_eventPrefix = 'bs23_chat_entry_collection';
    protected $_eventObject = 'chat_entry_collection';

    protected function _construct(): void
    {
        $this->_init(ChatEntry::class, ChatEntryResource::class);
    }
}
