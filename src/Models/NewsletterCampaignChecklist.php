<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use FerdinandFrank\LaravelMailChimpNewsletter\Collection;

/**
 * NewsletterCampaignChecklist
 * -----------------------
 * Represents a MailChimp checklist for a newsletter campaign.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterCampaignChecklist extends MailChimpModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'send-checklist';

    /**
     * Gets the items of the checklist that contain an error.
     *
     * @return Collection
     */
    public function getErrorItems() {
        return $this->items->filter(function ($item) {
            return $item->hasError();
        });
    }

    /**
     * Gets the items of this checklist.
     *
     * @return Collection
     */
    public function items() {
        $itemsInfo = $this->getAttributeValue('items');
        $items = new Collection();

        if (!$itemsInfo || !is_array($itemsInfo)) {
            return new Collection();
        }

        foreach ($itemsInfo as $itemData) {
            $items->push(new NewsletterCampaignChecklistItem($itemData));
        }

        return $items;
    }

}
