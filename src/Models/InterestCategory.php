<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

/**
 * InterestCategory
 * -----------------------
 * Represents a MailChimp interest category of a newsletter list.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class InterestCategory extends NewsletterListChildModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'interest-categories';

    /**
     * The MailChimp resource response name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_RESPONSE_NAME = 'categories';

    /**
     * Gets all interests within this interest category.
     *
     * @return \FerdinandFrank\LaravelMailChimpNewsletter\Collection
     */
    public function interests() {
        return Interest::forParent($this)->all();
    }

    /**
     * Creates a new interest category instance based on the data specified in the config file.
     *
     * @param null $listName
     * @param null $interestCategoryId
     *
     * @return InterestCategory
     */
    public static function getInterestCategoryFromConfig($listName = null, $interestCategoryId = null) {
        $listName = $listName ?? config('mailchimp_newsletter.default_list_name');

        $interestCategoryId = $interestCategoryId ??
                              config('mailchimp_newsletter.lists.' . $listName . '.default_interest_category_id');

        return static::findModel($interestCategoryId);
    }
}
