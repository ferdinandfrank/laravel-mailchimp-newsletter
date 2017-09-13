<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;


/**
 * NewsletterCampaignChecklistItem
 * -----------------------
 * Represents a MailChimp checklist item for a newsletter campaign.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterCampaignChecklistItem extends MailChimpModel {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'heading',
        'details'
    ];

    /**
     * Checks if the item has an error.
     *
     * @return bool
     */
    public function hasError() {
        return $this->type === 'error';
    }

    /**
     * Checks if the item is valid.
     *
     * @return bool
     */
    public function hasSuccess() {
        return $this->type === 'success';
    }

    /**
     * Checks if the item has a warning.
     *
     * @return bool
     */
    public function hasWarning() {
        return $this->type === 'warning';
    }

}
