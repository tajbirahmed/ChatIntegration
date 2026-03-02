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
    public const ENTRY_ID   = 'entry_id';
    public const NAME       = 'name';
    public const URL        = 'url';
    public const ICON       = 'icon';
    public const SORT_ORDER = 'sort_order';
    public const IS_ENABLED = 'is_enabled';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getEntryId(): ?int;
    public function setEntryId(int $entryId): static;

    public function getName(): string;
    public function setName(string $name): static;

    public function getUrl(): string;
    public function setUrl(string $url): static;

    public function getIcon(): ?string;
    public function setIcon(?string $icon): static;

    public function getSortOrder(): int;
    public function setSortOrder(int $sortOrder): static;

    public function getIsEnabled(): bool;
    public function setIsEnabled(bool $isEnabled): static;

    public function getCreatedAt(): ?string;
    public function getUpdatedAt(): ?string;
}
