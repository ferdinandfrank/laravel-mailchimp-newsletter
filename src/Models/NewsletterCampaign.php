<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use Exception;
use FerdinandFrank\LaravelMailChimpNewsletter\Exceptions\CampaignNotReadyException;
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
        'preview_text',
        'from_name',
        'reply_to',
        'template_id',
        'tracking_active',
        'social_card',
        'social_card_title',
        'social_card_description',
        'social_card_image_url'
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
        $subPath = '/wizard/neapolitan'; // the relative path to edit the campaign
        if ($this->isSent()) {
            $subPath = '/reports/summary';
        }

        return MailChimpHandler::getUrlToDashboard() . static::$RESOURCE_NAME . $subPath . '?id='
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
        if (!$this->canBeSend()) {
            throw new CampaignNotReadyException($this->checklist);
        }
        if (!is_array($testEmails)) {
            $testEmails = [$testEmails];
        }
        MailChimpHandler::post($this->getApiPath() . '/actions/test',
            ['test_emails' => $testEmails, 'send_type' => $sendType]);

        return $this;
    }

    /**
     * Schedules the campaign to the specified datetime. If the specified datetime is in the past, the campaign will be
     * send immediately.
     *
     * @param Carbon $scheduleTime
     *
     * @return $this
     * @throws Exception|CampaignNotReadyException
     */
    public function schedule(Carbon $scheduleTime) {
        if (!$this->canBeSend()) {
            throw new CampaignNotReadyException($this->checklist);
        }

        if ($scheduleTime->lte(Carbon::now())) {
            $this->send();
        } else {
            MailChimpHandler::post($this->getApiPath() . '/actions/schedule',
                ['schedule_time' => $scheduleTime->toAtomString()]);
        }

        return $this;
    }

    /**
     * Sends the campaign immediately.
     *
     * @return $this
     * @throws Exception|CampaignNotReadyException
     */
    public function send() {
        if (!$this->canBeSend()) {
            throw new CampaignNotReadyException($this->checklist);
        }

        MailChimpHandler::post($this->getApiPath() . '/actions/send');

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

        if ($recipientsInfo && is_array($recipientsInfo)) {
            $list = new NewsletterList();
            $list->forceFill($recipientsInfo);

            return $list;
        }

        return $recipientsInfo;
    }

    /**
     * Gets the checklist of this campaign.
     *
     * @return NewsletterCampaignChecklist
     */
    public function checklist() {
        return NewsletterCampaignChecklist::forParent($this)->getModel();
    }

    /**
     * Gets the content of this campaign.
     *
     * @return NewsletterCampaignContent
     */
    public function content() {
        return NewsletterCampaignContent::forParent($this)->getModel();
    }

    /**
     * Checks if the newsletter campaign can be send.
     *
     * @return bool
     */
    public function canBeSend() {
        return $this->checklist->is_ready;
    }

    /**
     * Checks if the newsletter campaign has already been sent.
     *
     * @return bool
     */
    public function isSent() {
        return $this->status === 'sent';
    }

    /**
     * Checks if the newsletter campaign is scheduled to be send.
     *
     * @return bool
     */
    public function isScheduled() {
        return $this->status === 'scheduled';
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

    public function getTrackingActiveAttribute() {
        return $this->tracking && array_has($this->tracking, 'opens') ? $this->tracking['opens'] : null;
    }

    public function getTemplateIdAttribute() {
        return $this->settings && array_has($this->settings, 'template_id') ? $this->settings['template_id'] : null;
    }

    public function getOpensAttribute() {
        return $this->report_summary && array_has($this->report_summary, 'opens') ? $this->report_summary['opens']
            : null;
    }

    public function getUniqueOpensAttribute() {
        return $this->report_summary && array_has($this->report_summary, 'unique_opens')
            ? $this->report_summary['unique_opens'] : null;
    }

    public function getOpenRateAttribute() {
        return $this->report_summary && array_has($this->report_summary, 'open_rate')
            ? $this->report_summary['open_rate'] : null;
    }

    public function getClicksAttribute() {
        return $this->report_summary && array_has($this->report_summary, 'clicks') ? $this->report_summary['clicks']
            : null;
    }

    public function getSubscriberClicksAttribute() {
        return $this->report_summary && array_has($this->report_summary, 'subscriber_clicks')
            ? $this->report_summary['subscriber_clicks'] : null;
    }

    public function getClickRateAttribute() {
        return $this->report_summary && array_has($this->report_summary, 'click_rate')
            ? $this->report_summary['click_rate'] : null;
    }

    public function getSocialCardImageUrlAttribute() {
        return $this->social_card && array_has($this->social_card, 'image_url')
            ? $this->social_card['image_url'] : null;
    }

    public function getSocialCardTitleAttribute() {
        return $this->social_card && array_has($this->social_card, 'title')
            ? $this->social_card['title'] : null;
    }

    public function getSocialDescriptionAttribute() {
        return $this->social_card && array_has($this->social_card, 'description')
            ? $this->social_card['description'] : null;
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

    public function setTrackingActiveAttribute($value) {
        $this->attributes['tracking']['opens'] = !!$value;
    }

    public function setTemplateIdAttribute($value) {
        $this->attributes['settings']['template_id'] = $value;
    }

    public function setRecipientsAttribute($value) {
        if (!is_array($value)) {
            $this->attributes['recipients']['list_id'] = $value;
        } else {
            $this->attributes['recipients'] = $value;
        }
    }

    public function setSocialCardImageUrlAttribute($value) {
        $this->attributes['social_card']['image_url'] = $value;
    }

    public function setSocialCardTitleAttribute($value) {
        $this->attributes['social_card']['title'] = $value;
    }

    public function setSocialCardDescriptionAttribute($value) {
        $this->attributes['social_card']['description'] = $value;
    }
}
