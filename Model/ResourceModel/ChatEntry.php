<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\ResourceModel;

use BS23\ChatIntegration\Api\Data\ChatEntryInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Chat Entry resource model – maps to bs23_chat_entry table.
 *
 * Also manages the bs23_chat_entry_store relation table so that each entry
 * can be scoped to one or more store views (store_id = 0 = All Store Views).
 */
class ChatEntry extends AbstractDb
{
    private const STORE_TABLE = 'bs23_chat_entry_store';

    protected function _construct(): void
    {
        $this->_init('bs23_chat_entry', 'entry_id');
    }

    /**
     * After loading a single entry, attach its store IDs.
     */
    protected function _afterLoad(AbstractModel $object): static
    {
        parent::_afterLoad($object);

        if ($object->getId()) {
            $storeIds = $this->lookupStoreIds((int) $object->getId());
            $object->setData(ChatEntryInterface::STORE_IDS, $storeIds ?: [0]);
        }

        return $this;
    }

    /**
     * After saving an entry, persist its store_ids to the relation table.
     */
    protected function _afterSave(AbstractModel $object): static
    {
        $this->saveStoreRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * Return all store_ids associated with the given entry_id.
     *
     * @return int[]
     */
    public function lookupStoreIds(int $entryId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable(self::STORE_TABLE), 'store_id')
            ->where('entry_id = ?', $entryId);

        return array_map('intval', $connection->fetchCol($select));
    }

    /**
     * Replace store associations for the given entry.
     *
     * A null / non-array store_ids means "no change" (e.g. repository callers
     * that don't deal with store scope). An empty array removes all rows.
     */
    private function saveStoreRelation(AbstractModel $object): void
    {
        $storeIds = $object->getData(ChatEntryInterface::STORE_IDS);

        if (!is_array($storeIds)) {
            return;
        }

        $connection = $this->getConnection();
        $table      = $this->getTable(self::STORE_TABLE);
        $entryId    = (int) $object->getId();

        $connection->delete($table, ['entry_id = ?' => $entryId]);

        $rows = [];
        foreach (array_unique(array_map('intval', $storeIds)) as $storeId) {
            $rows[] = ['entry_id' => $entryId, 'store_id' => $storeId];
        }

        if ($rows) {
            $connection->insertMultiple($table, $rows);
        }
    }
}
