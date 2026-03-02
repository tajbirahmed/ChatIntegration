<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Typed configuration reader for BS23 Chat Integration.
 *
 * All getters accept an optional store scope code and return validated,
 * type-safe values with sensible defaults.
 */
class Config
{
    // ── XML path constants ──────────────────────────────────────────────────
    private const XML_ENABLED         = 'bs23_chat_integration/general/enabled';
    private const XML_POSITION        = 'bs23_chat_integration/general/position';
    private const XML_LAYOUT_STYLE    = 'bs23_chat_integration/general/layout_style';
    private const XML_BUBBLE_SIZE     = 'bs23_chat_integration/appearance/bubble_size';
    private const XML_ICON_SIZE       = 'bs23_chat_integration/appearance/icon_size';
    private const XML_ICON_SPACING    = 'bs23_chat_integration/appearance/icon_spacing';
    private const XML_BUBBLE_BG_COLOR = 'bs23_chat_integration/appearance/bubble_bg_color';
    private const XML_HOVER_EFFECT    = 'bs23_chat_integration/appearance/hover_effect';
    private const XML_MAX_FILE_SIZE   = 'bs23_chat_integration/appearance/max_file_size';

    // ── Defaults (mirrors etc/config.xml) ───────────────────────────────────
    private const DEFAULT_POSITION    = 'lower_right';
    private const DEFAULT_LAYOUT      = 'vertical';
    private const DEFAULT_BUBBLE_SIZE = 56;
    private const DEFAULT_ICON_SIZE   = 24;
    private const DEFAULT_SPACING     = 12;
    private const DEFAULT_BG_COLOR    = '#25D366';
    private const DEFAULT_FILE_SIZE   = 512;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function isEnabled(?string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    public function getPosition(?string $scopeCode = null): string
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_POSITION,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return in_array($value, ['lower_right', 'lower_left', 'upper_right'], true)
            ? $value
            : self::DEFAULT_POSITION;
    }

    public function getLayoutStyle(?string $scopeCode = null): string
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_LAYOUT_STYLE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return in_array($value, ['vertical', 'horizontal', 'circular'], true)
            ? $value
            : self::DEFAULT_LAYOUT;
    }

    public function getBubbleSize(?string $scopeCode = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_BUBBLE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return $value > 0 ? $value : self::DEFAULT_BUBBLE_SIZE;
    }

    public function getIconSize(?string $scopeCode = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_ICON_SIZE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return $value > 0 ? $value : self::DEFAULT_ICON_SIZE;
    }

    public function getIconSpacing(?string $scopeCode = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_ICON_SPACING,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return $value > 0 ? $value : self::DEFAULT_SPACING;
    }

    /**
     * Return a validated hex color string or the default.
     */
    public function getBubbleBgColor(?string $scopeCode = null): string
    {
        $color = (string) $this->scopeConfig->getValue(
            self::XML_BUBBLE_BG_COLOR,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)
            ? $color
            : self::DEFAULT_BG_COLOR;
    }

    public function isHoverEffectEnabled(?string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_HOVER_EFFECT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    public function getMaxFileSize(?string $scopeCode = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_MAX_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        return $value > 0 ? $value : self::DEFAULT_FILE_SIZE;
    }
}
