<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\System\Config;

use BS23\ChatIntegration\Block\Adminhtml\Form\Element\ColorPicker as ColorPickerElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Renders an HTML5 native color picker for system configuration fields.
 *
 * The browser's built-in <input type="color"> stores values as 6-digit hex
 * strings (#rrggbb), which is exactly what Model\Config::getBubbleBgColor()
 * validates and returns.
 */
class ColorPicker extends Field
{
    /**
     * Replace the default text input with a colour picker input.
     * All other Field behaviour (label, scope switcher, etc.) is unchanged.
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return ColorPickerElement::swatchHtml(
            $element->getHtmlId(),
            $element->getName(),
            (string) $element->getValue(),
            '#25D366'
        );
    }
}
