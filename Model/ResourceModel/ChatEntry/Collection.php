<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\ResourceModel\ChatEntry;

use BS23\ChatIntegration\Model\ChatEntry;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry as ChatEntryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

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

    /**
     * Limit the collection to entries assigned to the given store view.
     *
     * Entries scoped to store_id = 0 ("All Store Views") are always included.
     *
     * @param int|Store $store
     */
    public function addStoreFilter($store, bool $withAdmin = true): static
    {
        $storeId  = $store instanceof Store ? (int) $store->getId() : (int) $store;
        $storeIds = $withAdmin
            ? [Store::DEFAULT_STORE_ID, $storeId]
            : [$storeId];

        $this->getSelect()
            ->join(
                ['store_table' => $this->getTable('bs23_chat_entry_store')],
                'main_table.entry_id = store_table.entry_id',
                []
            )
            ->where('store_table.store_id IN (?)', $storeIds)
            ->group('main_table.entry_id');

        return $this;
    }
}
