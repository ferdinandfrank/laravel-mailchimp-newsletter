<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

/**
 * Template
 * -----------------------
 * Represents a MailChimp newsletter template.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class Template extends MailChimpModel {

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME = 'templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'html'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date_created'];

}
