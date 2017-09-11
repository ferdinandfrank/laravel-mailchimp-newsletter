<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use Exception;
use FerdinandFrank\LaravelMailChimpNewsletter\MailChimpHandler;
use Illuminate\Support\Carbon;

/**
 * NewsletterCampaign
 * -----------------------
 * Represents a MailChimp newsletter campaign.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class NewsletterCampaign extends MailChimpModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'settings',
        'type',
        'recipients',
        'title',
        'subject_line',
        'from_name',
        'reply_to',
        'template_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_time', 'send_time'];

    /**
     * Gets the path to the details page of this model at MailChimp.
     *
     * @return string
     */
    public function getRemotePath() {
        return MailChimpHandler::getUrlToDashboard() . static::$RESOURCE_NAME . '/wizard/neapolitan?id='
               . $this->web_id;
    }

    /**
     * Sends a test email of the campaign to the specified email addresses.
     *
     * @param        $testEmails
     * @param string $sendType
     *
     * @return $this
     * @throws Exception
     */
    public function sendTest($testEmails, $sendType = 'html') {
        if (!is_array($testEmails)) {
            $testEmails = [$testEmails];
        }
        MailChimpHandler::post($this->getApiPath() . '/actions/test',
            ['test_emails' => $testEmails, 'send_type' => $sendType]);

        return $this;
    }

    /**
     * Schedules the campaign to the specified datetime.
     *
     * @param Carbon $scheduleTime
     *
     * @return $this
     * @throws Exception
     */
    public function schedule(Carbon $scheduleTime) {
        MailChimpHandler::post($this->getApiPath() . '/actions/schedule',
            ['schedule_time' => $scheduleTime->toAtomString()]);

        return $this;
    }

    /**
     * Unschedules the campaign, so it does not get send.
     *
     * @return $this
     * @throws Exception
     */
    public function unschedule() {
        MailChimpHandler::post($this->getApiPath() . '/actions/unschedule');

        return $this;
    }

    /**
     * Gets the recipient newsletter lists of this campaign.
     *
     * @return NewsletterList
     */
    public function recipients() {
        $recipientsInfo = $this->getAttributeValue('recipients');

        if ($recipientsInfo) {
            $list = new NewsletterList();
            $list->forceFill($recipientsInfo);

            return $list;
        }

        return null;
    }

    /**
     * Gets the checklist of this campaign.
     *
     * @return NewsletterCampaignChildModel
     */
    public function checklist() {
        return NewsletterCampaignChecklist::forParent($this)->getModel();
    }

    /**
     * Gets the content of this campaign.
     *
     * @return NewsletterCampaignChildModel
     */
    public function content() {
        return NewsletterCampaignContent::forParent($this)->getModel();
    }


    public function getTitleAttribute() {
        return $this->settings && array_has($this->settings, 'title') ? $this->settings['title'] : null;
    }

    public function getSubjectLineAttribute() {
        return $this->settings && array_has($this->settings, 'subject_line') ? $this->settings['subject_line'] : null;
    }

    public function getPreviewTextAttribute() {
        return $this->settings && array_has($this->settings, 'preview_text') ? $this->settings['preview_text'] : null;
    }

    public function getFromNameAttribute() {
        return $this->settings && array_has($this->settings, 'from_name') ? $this->settings['from_name'] : null;
    }

    public function getReplyToAttribute() {
        return $this->settings && array_has($this->settings, 'reply_to') ? $this->settings['reply_to'] : null;
    }

    public function getOpensAttribute() {
        return $this->tracking['opens'];
    }

    public function getTemplateIdAttribute() {
        return $this->settings && array_has($this->settings, 'template_id') ? $this->settings['template_id'] : null;
    }

    public function setTitleAttribute($value) {
        $this->attributes['settings']['title'] = $value;
    }

    public function setSubjectLineAttribute($value) {
        $this->attributes['settings']['subject_line'] = $value;
    }

    public function setPreviewTextAttribute($value) {
        $this->attributes['settings']['preview_text'] = $value;
    }

    public function setFromNameAttribute($value) {
        $this->attributes['settings']['from_name'] = $value;
    }

    public function setReplyToAttribute($value) {
        $this->attributes['settings']['reply_to'] = $value;
    }

    public function setOpensAttribute($value) {
        $this->attributes['tracking']['opens'] = $value;
    }

    public function setTemplateIdAttribute($value) {
        $this->attributes['settings']['template_id'] = $value;
    }

    public function setRecipientsAttribute($value) {
        if (!is_array($value)) {
            $this->attributes['recipients']['list_id'] = $value;
        }
        $this->attributes['recipients'] = $value;
    }
}
