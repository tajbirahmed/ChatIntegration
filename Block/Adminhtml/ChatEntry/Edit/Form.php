<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry\Edit;

use BS23\ChatIntegration\Block\Adminhtml\Form\Element\ColorPicker as ColorPickerElement;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

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

        $fieldset->addField('icon_bg_enabled', 'select', [
            'name'   => 'icon_bg_enabled',
            'label'  => __('Enable Icon Background Color'),
            'title'  => __('Enable Icon Background Color'),
            'values' => [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')],
            ],
            'after_element_html' => '<script>require(["jquery"],function($){'
                . '"use strict";'
                . 'function bs23ToggleIconBg(){'
                . '$("#row_icon_bg_color").toggle($("#icon_bg_enabled").val()==="1");}'
                . 'bs23ToggleIconBg();'
                . '$("#icon_bg_enabled").on("change",bs23ToggleIconBg);'
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
