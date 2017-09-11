<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MailChimp API Key
    |--------------------------------------------------------------------------
    |
    | The API key of the MailChimp account. Used to authenticate against the MailChimp API.
    | You can find yours here:
    | https://us10.admin.mailchimp.com/account/api-key-popup/
    |
    */

    'api_key' => env('MAILCHIMP_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default MailChimp List Name
    |--------------------------------------------------------------------------
    |
    | The default subscriber list name to use when no list is specifically specified in the function calls,
    | e.g., when adding a new subscriber.
    | The properties of this list have to be specified in the followed 'lists' array.
    |
    */

    'default_list_name' => env('MAILCHIMP_DEFAULT_LIST_NAME', 'subscribers'),

    /*
    |--------------------------------------------------------------------------
    | MailChimp Lists
    |--------------------------------------------------------------------------
    |
    | An array defining all MailChimp subscriber lists and their corresponding properties.
    | Necessary, so on the function calls only the name of the subscriber list needs to be specified and the
    | list id as well as other properties will automatically be fetched from this array.
    | As many lists as wanted can be specified in this array.
    |
    */

    'lists' => [

        env('MAILCHIMP_DEFAULT_LIST_NAME', 'subscribers') => [

            /*
             * The MailChimp list id. Check the MailChimp docs if you don't know
             * how to get this value:
             * http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id
             */
            'id' => env('MAILCHIMP_DEFAULT_LIST_ID'),

            'default_interest_category_id' => env('MAILCHIMP_DEFAULT_INTEREST_CATEGORY_ID'),

            'default_interest_id' => env('MAILCHIMP_DEFAULT_INTEREST_ID')
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MailChimp Encryption Setting
    |--------------------------------------------------------------------------
    |
    | Defines if an SSL connection shall be used when communication with the MailChimp API.
    | If you're having trouble with https connections, set this to false.
    |
    */

    'ssl' => true,

    /*
    |--------------------------------------------------------------------------
    | MailChimp List Member Status
    |--------------------------------------------------------------------------
    |
    | Lists the possible status values for a list member. Just for convenience.
    |
    */

    'list_member_status' => [
        'subscribed',
        'unsubscribed',
        'cleaned',
        'pending',

    ]
];
