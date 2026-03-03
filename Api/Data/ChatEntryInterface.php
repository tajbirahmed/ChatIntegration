<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Api\Data;

/**
 * Chat Entry data interface.
 *
 * Represents a single configurable chat platform entry (e.g. WhatsApp, Telegram).
 * Treat each entry as a generic deep-link entry to allow future expansion.
 */
interface ChatEntryInterface
{
    public const ICON_BG_COLOR   = 'icon_bg_color';
    public const ICON_BG_ENABLED = 'icon_bg_enabled';

    public const STORE_IDS  = 'store_ids';

    public const ENTRY_ID   = 'entry_id';
    public const NAME       = 'name';
    public const URL        = 'url';
    public const ICON       = 'icon';
    public const SORT_ORDER = 'sort_order';
    public const IS_ENABLED = 'is_enabled';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getEntryId(): ?int;
    public function setEntryId(int $entryId): self;

    public function getName(): string;
    public function setName(string $name): self;

    public function getUrl(): string;
    public function setUrl(string $url): self;

    public function getIcon(): ?string;
    public function setIcon(?string $icon): self;

    public function getIconBgColor(): ?string;
    public function setIconBgColor(?string $color): self;

    public function getIconBgEnabled(): bool;
    public function setIconBgEnabled(bool $enabled): self;

    public function getSortOrder(): int;
    public function setSortOrder(int $sortOrder): self;

    public function getIsEnabled(): bool;
    public function setIsEnabled(bool $isEnabled): self;

    public function getCreatedAt(): ?string;
    public function getUpdatedAt(): ?string;

    /** @return int[] */
    public function getStoreIds(): array;

    /** @param int[] $storeIds */
    public function setStoreIds(array $storeIds): self;
}
