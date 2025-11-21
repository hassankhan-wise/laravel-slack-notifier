<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Method
    |--------------------------------------------------------------------------
    |
    | This option controls the default notification method used by the package.
    | Supported: "webhook", "api"
    |
    */

    'default_method' => env('SLACK_DEFAULT_METHOD', 'webhook'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Slack Incoming Webhook URL here.
    | You can get this from: https://api.slack.com/messaging/webhooks
    |
    */

    'webhook_url' => env('SLACK_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Web API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Slack Bot Token and default channel for Web API method.
    | Get your token from: https://api.slack.com/apps
    |
    */

    'api' => [
        'token' => env('SLACK_BOT_TOKEN'),
        'default_channel' => env('SLACK_DEFAULT_CHANNEL', '#general'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for HTTP requests to Slack API.
    |
    */

    'timeout' => env('SLACK_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Notifications
    |--------------------------------------------------------------------------
    |
    | You can disable notifications globally (useful for testing/development)
    |
    */

    'enabled' => env('SLACK_NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Default username and icon for the bot when sending messages
    |
    */

    'defaults' => [
        'username' => env('SLACK_BOT_USERNAME', 'Laravel Bot'),
        'icon_emoji' => env('SLACK_BOT_ICON', ':robot_face:'),
    ],

];
