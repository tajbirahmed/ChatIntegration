<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Admin form container for Chat Entry add/edit page.
 */
class Edit extends Container
{
    public function __construct(
        Context $context,
        private readonly Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _construct(): void
    {
        $this->_objectId   = 'entry_id';
        $this->_blockGroup = 'BS23_ChatIntegration';
        $this->_controller = 'adminhtml_chatEntry';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Chat Entry'));
        $this->buttonList->add(
            'save_and_continue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit']],
                ],
            ],
            -100
        );
    }

    public function getHeaderText(): \Magento\Framework\Phrase
    {
        $entry = $this->coreRegistry->registry('bs23_chat_entry');

        if ($entry && $entry->getId()) {
            return __('Edit Chat Entry "%1"', $this->escapeHtml($entry->getName()));
        }

        return __('New Chat Entry');
    }
}
