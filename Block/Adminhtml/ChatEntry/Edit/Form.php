<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry\Edit;

use BS23\ChatIntegration\Block\Adminhtml\Form\Element\ColorPicker as ColorPickerElement;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Admin edit/add form for a single Chat Entry.
 *
 * Uses the classic Generic form block approach — no UI component form needed.
 * All fields are rendered server-side with Magento's form widget.
 */
class Form extends Generic
{
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly SystemStore $systemStore,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): static
    {
        /** @var array $entryData */
        $entryData = $this->dataPersistor->get('bs23_chat_entry') ?? [];

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $form->setUseContainer(true);

        // ── Primary key (hidden) ─────────────────────────────────────────
        $form->addField('entry_id', 'hidden', ['name' => 'entry_id']);

        // ── Main fieldset ────────────────────────────────────────────────
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Chat Entry Details'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Chat Name'),
            'title'    => __('Chat Name'),
            'required' => true,
            'class'    => 'validate-length maximum-length-255',
        ]);

        $fieldset->addField('url', 'text', [
            'name'     => 'url',
            'label'    => __('Deep Link URL'),
            'title'    => __('Deep Link URL'),
            'required' => true,
            'class'    => 'validate-url',
            'note'     => __('e.g. https://wa.me/1234567890 — opens in new tab.'),
        ]);

        $fieldset->addField('icon', 'file', [
            'name'  => 'icon_file',
            'label' => __('Chat Icon'),
            'title' => __('Chat Icon'),
            'note'  => __('Allowed: SVG, PNG, JPG, WebP. Max 512 KB. Leave blank to keep existing icon.'),
        ]);

        // Show existing icon as a preview note when editing
        if (!empty($entryData['icon'])) {
            $fieldset->addField('icon_preview', 'note', [
                'label' => __('Current Icon'),
                'text'  => sprintf(
                    '<img src="%s" alt="%s" style="max-height:48px;max-width:48px;"/>',
                    $this->escapeUrl(
                        $this->_storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . 'bs23/chatentry/' . ltrim((string) $entryData['icon'], '/')
                    ),
                    $this->escapeHtmlAttr((string) ($entryData['name'] ?? ''))
                ),
            ]);
        }

        // ── Icon background preview (computed before fields so values are available) ──
        $bgEnabled = isset($entryData['icon_bg_enabled']) && $entryData['icon_bg_enabled'] === '1';
        $initialBgColor = !empty($entryData['icon_bg_color'])
            ? $this->escapeHtmlAttr((string) $entryData['icon_bg_color'])
            : '#25D366';
        $previewBg = $bgEnabled ? $initialBgColor : '#ffffff';

        $previewIconHtml = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"'
            . ' fill="#555" width="22" height="22" aria-hidden="true">'
            . '<path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>'
            . '</svg>';
        if (!empty($entryData['icon'])) {
            $previewIconHtml = '<img src="' . $this->escapeUrl(
                $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . 'bs23/chatentry/' . ltrim((string) $entryData['icon'], '/')
            ) . '" alt="" width="22" height="22" style="object-fit:contain;display:block;">';
        }

        // Preview bubble is attached to icon_bg_enabled so it is ALWAYS visible,
        // regardless of whether the color row is shown or hidden.
        $fieldset->addField('icon_bg_enabled', 'select', [
            'name'   => 'icon_bg_enabled',
            'label'  => __('Enable Icon Background Color'),
            'title'  => __('Enable Icon Background Color'),
            'values' => [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')],
            ],
            'after_element_html' =>
                // ── Live preview bubble ──────────────────────────────────
                '<div style="margin-top:10px;display:flex;align-items:center;gap:12px;">'
                . '<div id="bs23_icon_bg_preview"'
                . ' title="' . $this->escapeHtmlAttr((string) __('Live preview – shows how the icon will look on the storefront')) . '"'
                . ' style="display:inline-flex;align-items:center;justify-content:center;'
                . 'width:46px;height:46px;border-radius:50%;'
                . 'background-color:' . $previewBg . ';'
                . 'box-shadow:0 2px 10px rgba(0,0,0,.18);'
                . 'border:1px solid rgba(0,0,0,.08);'
                . 'transition:background-color .2s ease,box-shadow .2s ease;flex-shrink:0;">'
                . $previewIconHtml
                . '</div>'
                . '<small style="color:#888;line-height:1.4;">'
                . $this->escapeHtml((string) __('Live preview of the icon button as it will appear on the storefront.'))
                . '</small>'
                . '</div>'
                // ── Combined JS: row toggle + live preview sync ──────────
                . '<script>require(["jquery"],function($){"use strict";'
                // Sync preview background to the currently selected color (or white when disabled)
                . 'function bs23SyncPreview(){'
                . '  var on=$("#icon_bg_enabled").val()==="1";'
                . '  var color=on?$("#icon_bg_color").val():"#ffffff";'
                . '  $("#bs23_icon_bg_preview").css({'
                . '    "background-color":color,'
                . '    "box-shadow":on?"0 2px 10px rgba(0,0,0,.18)":"0 1px 4px rgba(0,0,0,.08)"'
                . '  });'
                . '  $("#row_icon_bg_color").toggle(on);'
                . '}'
                . 'bs23SyncPreview();'
                . '$("#icon_bg_enabled").on("change",bs23SyncPreview);'
                // Update preview in real-time as the color picker value changes
                . '$(document).on("input","#icon_bg_color_cp",function(){'
                . '  $("#bs23_icon_bg_preview").css("background-color",this.value);'
                . '});'
                . '});</script>',
        ]);

        $fieldset->addField('icon_bg_color', ColorPickerElement::class, [
            'name'  => 'icon_bg_color',
            'label' => __('Icon Background Color'),
            'title' => __('Icon Background Color'),
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name'  => 'sort_order',
            'label' => __('Sort Order'),
            'title' => __('Sort Order'),
            'class' => 'validate-number',
            'value' => '0',
        ]);

        // ── Store View scope ─────────────────────────────────────────────
        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'multiselect', [
                'name'     => 'store_ids[]',
                'label'    => __('Store View'),
                'title'    => __('Store View'),
                'required' => true,
                'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                'note'     => __('Select one or more store views, or "All Store Views".'),
            ]);
        }

        $fieldset->addField('is_enabled', 'select', [
            'name'   => 'is_enabled',
            'label'  => __('Enabled'),
            'title'  => __('Enabled'),
            'values' => [
                ['value' => '1', 'label' => __('Yes')],
                ['value' => '0', 'label' => __('No')],
            ],
        ]);

        // ── Populate form with existing data ─────────────────────────────
        if (!empty($entryData)) {
            $form->setValues($entryData);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
