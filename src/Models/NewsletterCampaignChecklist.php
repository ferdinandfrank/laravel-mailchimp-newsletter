<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

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

}
