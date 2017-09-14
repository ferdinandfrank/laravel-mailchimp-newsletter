<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use FerdinandFrank\LaravelMailChimpNewsletter\Collection;
use FerdinandFrank\LaravelMailChimpNewsletter\MailChimpHandler;

/**
 * NewsletterListMemberActivity
 * -----------------------
 * Represents the activity of a member of a MailChimp newsletter list.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterListMemberActivity extends NewsletterListChildModel {

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
    protected $dates = ['timestamp'];

    public function isOpen() {
        return $this->action === 'open';
    }

    public function isClick() {
        return $this->action === 'click';
    }

    public function isSent() {
        return $this->action === 'sent';
    }

    public function isUnsub() {
        return $this->action === 'unsub';
    }

}

