<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Assign all pre-existing chat entries to "All Store Views" (store_id = 0).
 *
 * When per-store-view scope was introduced the bs23_chat_entry_store relation
 * table did not yet exist.  This patch back-fills store_id = 0 for every
 * entry that was created before the feature landed, ensuring they continue
 * to appear on every store front without requiring manual re-save.
 */
class AssignAllStoreViewsToExistingEntries implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $entryTable = $this->moduleDataSetup->getTable('bs23_chat_entry');
        $storeTable = $this->moduleDataSetup->getTable('bs23_chat_entry_store');

        $entryIds = $connection->fetchCol(
            $connection->select()->from($entryTable, 'entry_id')
        );

        foreach ($entryIds as $entryId) {
            $connection->insertOnDuplicate(
                $storeTable,
                ['entry_id' => (int) $entryId, 'store_id' => 0]
            );
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
