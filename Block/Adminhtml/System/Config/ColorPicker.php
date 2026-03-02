<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\System\Config;

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
        $element->setType('color');

        // Ensure the stored value is a valid 6-digit hex so the browser
        // colour picker can parse it; fall back to the default green.
        $value = (string) $element->getValue();
        if (!preg_match('/^#[A-Fa-f0-9]{6}$/', $value)) {
            // Expand 3-digit shorthand to 6-digit if needed
            if (preg_match('/^#([A-Fa-f0-9])([A-Fa-f0-9])([A-Fa-f0-9])$/', $value, $m)) {
                $value = '#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3];
            } else {
                $value = '#25D366';
            }
            $element->setValue($value);
        }

        return $element->getElementHtml();
    }
}
