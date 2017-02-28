<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
	
	'mailchimp' => [
        'listid' => env('LIST_ID'),
    ],
	'klaviyo' => [
        'listid1' => env('LIST_ID_STEP1'),
        'listid2' => env('LIST_ID_STEP2'),
        'apikey' => env('API_KEY_KLAVIYO'),
    ],
	
	'giact' => [
        'ApiMode' => env('API_MODE'),
        'ApiUsername' => env('API_USERNAME'),
        'ApiPassword' => env('API_PASSWORD'),
    ],
	
	'multisubscription' => [
        'fundconfirmationPlanId' => env('FUNDCONFIRMATION'),
        'signturePlanId' => env('SIGNTURE'),
        'checkoutlinkPlanId' => env('CHECKOUTLINK'),
        'bankauthlinkPlanId' => env('BANKAUTHLINK'),
    ],
	
	'rooturl' => [
        'curl' => env('C_URL'),
    ],
	
	
];
