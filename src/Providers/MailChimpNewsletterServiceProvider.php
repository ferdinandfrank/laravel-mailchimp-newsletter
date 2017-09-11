<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Providers;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\ServiceProvider;

/**
 * MailChimpNewsletterServiceProvider
 * -----------------------
 * Provides the publishing command for the "laravel-mailchimp-newsletter" package to publish the config file.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class MailChimpNewsletterServiceProvider extends ServiceProvider {

    /**
     * Path to the config file of the package.
     *
     * @var string
     */
    private $configFilePath = __DIR__ . '/../../config/mailchimp_newsletter.php';

    protected $defer = false;

    /**
     * Registers the service provider.
     */
    public function register() {
        $this->mergeConfigFrom($this->configFilePath, 'mailchimp_newsletter');
    }

    /**
     * Bootstraps the service provider.
     */
    public function boot() {
        $this->publishes([
            $this->configFilePath => config_path('mailchimp_newsletter.php'),
        ], 'config');

        $this->app->singleton(MailChimp::class, function () {
            $mailChimp = new MailChimp(config('mailchimp_newsletter.api_key'));
            $mailChimp->verify_ssl = config('mailchimp_newsletter.ssl', true);

            return $mailChimp;
        });

        $this->app->alias(MailChimp::class, 'mailchimp');
    }
}
