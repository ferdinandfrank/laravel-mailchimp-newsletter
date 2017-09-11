<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

/**
 * NewsletterCampaignContent
 * -----------------------
 * Represents the content of a MailChimp newsletter campaign.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterCampaignContent extends MailChimpModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'content';

}
