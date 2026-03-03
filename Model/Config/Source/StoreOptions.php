<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

/**
 * Option source for the store-view multiselect in the Chat Entry UI form.
 *
 * Returns a flat list that includes "All Store Views" (id = 0),
 * mirroring what the legacy Generic form block used via getStoreValuesForForm().
 */
class StoreOptions implements OptionSourceInterface
{
    public function __construct(private readonly Store $systemStore)
    {
    }

    public function toOptionArray(): array
    {
        return $this->systemStore->getStoreValuesForForm(false, true);
    }
}
