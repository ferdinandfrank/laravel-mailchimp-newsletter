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

    use IsSearchable;

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
     * Gets the array of the specified array that contains the results of a search query.
     *
     * @param array $response
     *
     * @return array
     */
    protected static function getSearchResultsFromResponse(array $response) {
        return $response['full_search']['members'];
    }


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
     * Gets the activity data for this member.
     *
     * @return Collection
     */
    public function activity() {
        return NewsletterListMemberActivity::forParent($this)->all();
    }

    /**
     * Checks if the member has the interest with the specified id.
     *
     * @param $interestId
     *
     * @return bool
     */
    public function hasInterest($interestId) {
        return $this->interests->find($interestId) != null;
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
        if(str_contains($this->email_address, '@')) {
            return MailChimpHandler::getSubscriberHash($this->email_address);
        }
        return $this->email_address;
    }

    /**
     * Sets the state of this user as subscribed.
     */
    public function subscribe() {
        $this->status = 'subscribed';
        $this->save();
    }

    /**
     * Sets the state of this user as pending.
     */
    public function activate() {
        $this->status = 'pending';
        $this->save();
    }

    /**
     * Checks if the member is actively subscribed to the list.
     *
     * @return bool
     */
    public function isSubscribed() {
        return $this->status === 'subscribed';
    }

    /**
     * Checks if the member has unsubscribed from the list.
     *
     * @return bool
     */
    public function isUnsubscribed() {
        return $this->status === 'unsubscribed';
    }

    /**
     * Checks if the member needs to conform his account.
     *
     * @return bool
     */
    public function isPending() {
        return $this->status === 'pending';
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
        return $this->merge_fields && array_has($this->merge_fields, 'FNAME') ? $this->merge_fields['FNAME'] : null;
    }

    public function getLastNameAttribute() {
        return $this->merge_fields && array_has($this->merge_fields, 'LNAME') ? $this->merge_fields['LNAME'] : null;
    }

    public function getAvgOpenRate() {
        return $this->stats && array_has($this->stats, 'avg_open_rate') ? $this->stats['avg_open_rate'] : null;
    }

    public function getAvgClickRate() {
        return $this->stats && array_has($this->stats, 'avg_click_rate') ? $this->stats['avg_click_rate'] : null;
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

