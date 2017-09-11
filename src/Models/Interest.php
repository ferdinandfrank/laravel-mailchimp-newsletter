<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

/**
 * Interest
 * -----------------------
 * Represents a MailChimp interest of a interest category.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class Interest extends MailChimpModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'interests';

    /**
     * Gets the resource parent of the model.
     *
     * @return MailChimpModel
     */
    public function getParent() {
        if ($this->parent) {
            return $this->parent;
        }

        $this->parent = $this->interest_category_id ? (new InterestCategory($this->interest_category_id))
            : InterestCategory::getInterestCategoryFromConfig();

        return $this->parent;
    }

}
