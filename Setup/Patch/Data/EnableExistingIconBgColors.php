<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrate entries created before the icon_bg_enabled toggle existed.
 *
 * When the icon_bg_enabled column was added (db_schema default = 0), all
 * existing rows got 0 even if icon_bg_color was already set. This patch
 * enables the background for those entries so their saved color is visible
 * on the storefront.
 */
class EnableExistingIconBgColors implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table      = $this->moduleDataSetup->getTable('bs23_chat_entry');

        $connection->update(
            $table,
            ['icon_bg_enabled' => 1],
            ['icon_bg_color IS NOT NULL', "icon_bg_color != ''"]
        );
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
