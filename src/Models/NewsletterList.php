<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use FerdinandFrank\LaravelMailChimpNewsletter\MailChimpHandler;

/**
 * NewsletterList
 * -----------------------
 * Represents a MailChimp newsletter list.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterList extends MailChimpModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'lists';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date_created'];

    /**
     * Creates a new list instance based on the data specified in the config file.
     *
     * @param string|null $listName
     *
     * @return MailChimpModel
     */
    public static function getListFromConfig($listName = null) {
        $listName = $listName ?? config('mailchimp_newsletter.default_list_name');
        $listId = config('mailchimp_newsletter.lists.' . $listName . '.id');

        return NewsletterList::findModel($listId);
    }

    /**
     * Gets the path to the details page of this model at MailChimp.
     *
     * @return string
     */
    public function getRemotePath() {
        return MailChimpHandler::getUrlToDashboard() . "lists/members/?id={$this->web_id}";
    }
}
