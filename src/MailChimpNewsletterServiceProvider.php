<?php

namespace FerdinandFrank\MailChimpNewsletter;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\ServiceProvider;

class MailChimpNewsletterServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function boot() {
        $this->mergeConfigFrom(__DIR__ . '/../config/mailchimp_newsletter.php', 'mailchimp_newsletter');

        $this->publishes([
            __DIR__ . '/../config/mailchimp_newsletter.php' => config_path('mailchimp_newsletter.php'),
        ]);
    }

    public function register() {
        $this->app->singleton(MailChimpNewsletter::class, function () {
            $mailChimp = new Mailchimp(config('mailchimp_newsletter.apiKey'));

            $mailChimp->verify_ssl = config('mailchimp_newsletter.ssl', true);

            $configuredLists = MailChimpNewsletterListCollection::createFromConfig(config('mailchimp_newsletter'));

            return new MailChimpNewsletter($mailChimp, $configuredLists);
        });

        $this->app->alias(MailChimpNewsletter::class, 'mailchimp_newsletter');
    }
}
