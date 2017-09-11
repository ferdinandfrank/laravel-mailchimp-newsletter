<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

/**
 * NewsletterListActivity
 * -----------------------
 * Represents the activity of a MailChimp newsletter list.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterListActivity extends NewsletterListChildModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'activity';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['day'];

}

