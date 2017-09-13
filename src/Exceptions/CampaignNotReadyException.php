<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Exceptions;
use FerdinandFrank\LaravelMailChimpNewsletter\Models\NewsletterCampaignChecklist;

/**
 * CampaignNotReadyException
 * -----------------------
 * Exception to demonstrate that a campaign is not ready to send yet, when a send action of the campaign has been called.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class CampaignNotReadyException extends \Exception {

    private $checklist;

    /**
     * Construct the exception.
     *
     * @param NewsletterCampaignChecklist $checklist
     */
    public function __construct(NewsletterCampaignChecklist $checklist) {
        $this->checklist = $checklist;
        parent::__construct('Campaign is not ready to send: ' . $checklist->getErrorItems()->toJson());
    }

    /**
     * Gets the Checklist value of the CampaignNotReadyException.
     *
     * @return NewsletterCampaignChecklist
     */
    public function getChecklist() {
        return $this->checklist;
    }

}