<?php

namespace FerdinandFrank\MailChimpNewsletter;

use Illuminate\Support\Facades\Facade;

class MailChimpNewsletterFacade extends Facade {

    public static function getFacadeAccessor() {
        return 'newsletter';
    }
}
