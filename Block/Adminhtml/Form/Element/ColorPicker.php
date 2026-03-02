<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Escaper;

/**
 * Reusable HTML5 color-picker form element for Magento Generic admin forms.
 *
 * Usage in any Generic form block:
 *   $fieldset->addField('field_id', ColorPicker::class, ['label' => ...])
 *
 * Also exposes a static normalizeHex() helper consumed by
 * Block\Adminhtml\System\Config\ColorPicker for shared validation logic.
 */
class ColorPicker extends Text
{
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('color');
    }

    public function getElementHtml(): string
    {
        return self::swatchHtml(
            $this->getHtmlId(),
            $this->getName(),
            (string) $this->getValue()
        );
    }

    /**
     * Render a swatch-style color picker: small colored button + hidden native
     * <input type="color"> + hidden text input that carries the submitted value.
     *
     * Shared by Block\Adminhtml\System\Config\ColorPicker so both pickers look
     * identical regardless of the form type (Generic or System Config).
     *
     * @param string $id      HTML element id for the hidden value input
     * @param string $name    Form field name (submitted to server)
     * @param string $value   Current raw hex value (normalised internally)
     * @param string $default Fallback colour when $value is empty/invalid
     */
    public static function swatchHtml(
        string $id,
        string $name,
        string $value,
        string $default = '#ffffff'
    ): string {
        $hex  = self::normalizeHex($value, $default);
        $eId  = htmlspecialchars($id,   ENT_QUOTES, 'UTF-8');
        $eName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $eHex = htmlspecialchars($hex,  ENT_QUOTES, 'UTF-8');

        // oninput handler: sync swatch colour, label text, and hidden value
        $onInput = "var v=this.value;"
            . "document.getElementById('{$eId}_sw').style.background=v;"
            . "document.getElementById('{$eId}_lb').textContent=v;"
            . "document.getElementById('{$eId}').value=v;";

        return '<span style="display:inline-flex;align-items:center;gap:8px;vertical-align:middle;">'
            // Visible swatch button — clicking opens the native picker
            . '<button type="button" id="' . $eId . '_sw"'
            . ' onclick="document.getElementById(\'' . $eId . '_cp\').click()"'
            . ' title="Pick colour"'
            . ' style="width:32px;height:32px;min-width:32px;background:' . $eHex . ';'
            . 'border:1px solid #aaa;border-radius:4px;cursor:pointer;padding:0;"></button>'
            // Hex label
            . '<span id="' . $eId . '_lb"'
            . ' style="font-family:monospace;font-size:12px;color:#555;">' . $eHex . '</span>'
            // Native color input — invisible but functional
            . '<input type="color" id="' . $eId . '_cp" value="' . $eHex . '"'
            . ' style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;" tabindex="-1"'
            . ' oninput="' . htmlspecialchars($onInput, ENT_QUOTES, 'UTF-8') . '">'
            // Hidden input carries the value on form submit
            . '<input type="hidden" id="' . $eId . '" name="' . $eName . '" value="' . $eHex . '">'
            . '</span>';
    }

    /**
     * Normalize a hex color string to 6-digit #rrggbb format.
     *
     * Returns $default when the value is empty or invalid.
     * Expands 3-digit shorthand (#abc → #aabbcc) automatically.
     * Shared by Block\Adminhtml\System\Config\ColorPicker.
     */
    public static function normalizeHex(string $value, string $default = '#ffffff'): string
    {
        if ($value === '') {
            return $default;
        }

        // Expand 3-digit shorthand → 6-digit
        if (preg_match('/^#([A-Fa-f0-9])([A-Fa-f0-9])([A-Fa-f0-9])$/', $value, $m)) {
            return '#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3];
        }

        return preg_match('/^#[A-Fa-f0-9]{6}$/', $value) ? $value : $default;
    }
}
