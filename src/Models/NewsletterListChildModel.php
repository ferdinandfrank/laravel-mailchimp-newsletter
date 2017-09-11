<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use DateTime;
use FerdinandFrank\LaravelMailChimpNewsletter\Collection;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\MassAssignmentException;

/**
 * NewsletterListChildModel
 * -----------------------
 * Abstract model class to represent a child model of a MailChimp newsletter list.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
abstract class NewsletterListChildModel extends MailChimpModel {

    /**
     * Gets the resource parent of the model.
     *
     * @return MailChimpModel
     */
    public function getParent() {
        if ($this->parent) {
            return $this->parent;
        }

        $this->parent = $this->list_id ? (new NewsletterList($this->list_id)) : NewsletterList::getListFromConfig();

        return $this->parent;
    }
}