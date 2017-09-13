<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use FerdinandFrank\LaravelMailChimpNewsletter\Collection;
use FerdinandFrank\LaravelMailChimpNewsletter\MailChimpHandler;

/**
 * NewsletterListMember
 * -----------------------
 * Represents a member of a MailChimp newsletter list.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterListMember extends NewsletterListChildModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_address',
        'email_type',
        'status',
        'interests',
        'merge_fields',
        'first_name',
        'last_name',
        'language',
        'vip',
        'location'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['timestamp_signup', 'timestamp_opt', 'last_changed'];

    /**
     * Gets the interests of this member.
     *
     * @return Collection
     */
    public function interests() {
        $interestsInfo = $this->getAttributeValue('interests');
        $interests = new Collection();

        if (!$interestsInfo || !is_array($interestsInfo)) {
            return new Collection();
        }

        $interestCategories = InterestCategory::all();
        foreach ($interestsInfo as $id => $active) {
            if ($active) {
                foreach ($interestCategories as $interestCategory) {
                    $interest = $interestCategory->interests->find($id);
                    if ($interest) {
                        $interests->push($interest);
                        break;
                    }
                }
            }
        }

        return $interests;
    }

    /**
     * Checks if the member has the interest with the specified id.
     *
     * @param $interestId
     *
     * @return bool
     */
    public function hasInterest($interestId) {
        return $this->interests()->find($interestId) != null;
    }

    /**
     * Gets the name of the identifying key of the model.
     *
     * @return string
     */
    public function getRouteKeyName() {
        return 'email_address';
    }

    /**
     * Gets the key value of the model that is used in the URLs of the model.
     *
     * @return mixed
     */
    public function getRouteKey() {
        return MailChimpHandler::getSubscriberHash($this->email_address);
    }

    /**
     * Checks if the member is actively subscribed to a list.
     *
     * @return bool
     */
    public function isSubscribed() {
        return $this->status === 'subscribed';
    }

    /**
     * Finds only subscribed members.
     *
     * @param string              $email
     * @param MailChimpModel|null $parent
     *
     * @return null
     */
    public static function findSubscribed(string $email, MailChimpModel $parent = null) {
        $id = MailChimpHandler::getSubscriberHash($email);

        $model = static::forParent($parent)->findModel($id);

        if ($model && $model->isSubscribed()) {
            return $model;
        }

        return null;
    }

    public function getFirstNameAttribute() {
        return $this->merge_fields['FNAME'];
    }

    public function getLastNameAttribute() {
        return $this->merge_fields['LNAME'];
    }

    /**
     * Sets the list member's first name.
     *
     * @param  string $value
     */
    public function setFirstNameAttribute($value) {
        $this->attributes['merge_fields']['FNAME'] = $value;
    }

    /**
     * Sets the list member's last name.
     *
     * @param  string $value
     */
    public function setLastNameAttribute($value) {
        $this->attributes['merge_fields']['LNAME'] = $value;
    }
}

