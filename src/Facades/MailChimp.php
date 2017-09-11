<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * MailChimp
 * -----------------------
 * Defines the facade for the underlying MailChimp package.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class MailChimp extends Facade {

    public static function getFacadeAccessor() {
        return 'mailchimp';
    }
}
