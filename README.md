# Laravel Slack Notifier

A comprehensive Laravel package for sending beautiful Slack notifications using both Incoming Webhooks and the Slack Web API. Send simple text messages or rich block messages with an elegant, fluent interface.

[![Latest Stable Version](https://img.shields.io/packagist/v/hkwise/laravel-slack-notifier.svg?style=flat-square)](https://packagist.org/packages/hkwise/laravel-slack-notifier)
[![Total Downloads](https://img.shields.io/packagist/dt/hkwise/laravel-slack-notifier.svg?style=flat-square)](https://packagist.org/packages/hkwise/laravel-slack-notifier)

## Features

✅ **Dual Sending Methods**: Support for both Incoming Webhooks and Web API (`chat.postMessage`) <br/>
✅ **Simple Text Messages**: Send quick text notifications <br/>
✅ **Block Messages**: Create rich, interactive messages with blocks <br/>
✅ **Fluent Interface**: Chain methods for clean, readable code <br/>
✅ **Easy Configuration**: Simple `.env` based configuration <br/>
✅ **Test Command**: Built-in artisan command to test your setup <br/>
✅ **Laravel Auto-Discovery**: Zero configuration setup <br/>
✅ **Highly Customizable**: Control username, icon, channel, and more <br/>

## Requirements

- PHP 8.1 or higher
- Laravel 9.x, 10.x, 11.x, or 12.x
- Guzzle HTTP Client 7.x

## Installation

Install the package via Composer:

```bash
composer require hkwise/laravel-slack-notifier
```

### Publish Configuration (Optional)

The package works out of the box with environment variables, but you can publish the config file if needed:

```bash
php artisan vendor:publish --tag=slack-notifier-config
```

This will create a `config/slack-notifier.php` file.

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```env
# Default method: webhook or api
SLACK_DEFAULT_METHOD=webhook

# Webhook Configuration
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Web API Configuration
SLACK_BOT_TOKEN=xoxb-your-bot-token-here
SLACK_DEFAULT_CHANNEL=#general

# Optional Settings
SLACK_TIMEOUT=10
SLACK_NOTIFICATIONS_ENABLED=true
SLACK_BOT_USERNAME="Laravel Bot"
SLACK_BOT_ICON=:robot_face:
```

### Getting Slack Credentials

#### Option 1: Incoming Webhooks (Recommended for Simple Use)

1. Go to [Slack Apps](https://api.slack.com/apps)
2. Create a new app or select an existing one
3. Navigate to **"Incoming Webhooks"**
4. Activate Incoming Webhooks
5. Click **"Add New Webhook to Workspace"**
6. Select the channel where you want to post messages
7. Copy the webhook URL and add it to your `.env` as `SLACK_WEBHOOK_URL`

**Pros**: Simple setup, no token management <br/>
**Cons**: Can only post to the pre-configured channel

#### Option 2: Web API (Recommended for Multiple Channels)

1. Go to [Slack Apps](https://api.slack.com/apps)
2. Create a new app or select an existing one
3. Navigate to **"OAuth & Permissions"**
4. Under **"Scopes"** → **"Bot Token Scopes"**, add these permissions:
   - `chat:write` (Send messages)
   - `chat:write.public` (Send messages to public channels without joining)
   - `groups:read` (May be required for private channels)
   - `groups:write` (May be required for private channels)
5. Click **"Install to Workspace"** at the top
6. Copy the **"Bot User OAuth Token"** (starts with `xoxb-`)
7. Add it to your `.env` as `SLACK_BOT_TOKEN`

**Pros**: Can send to any channel dynamically <br/>
**Cons**: Requires more setup, need to manage tokens

### Channel Formatting

You can specify channels in multiple ways:
- **With hash**: `#general`, `#my-channel`
- **Without hash**: `general`, `my-channel`
- **Channel ID**: `C1234567890` (starts with 'C')
- **Direct messages**: `@username` or user ID `U1234567890`

**For Private Channels:**
- Your bot must be invited to the private channel before it can send messages
- In the private channel, type: `/invite @YourBotName`
- Or click channel name → Integrations → Add apps → Select your bot

## Usage

### Basic Text Message (Webhook)

```php
use HKWise\LaravelSlackNotifier\Facades\SlackNotifier;

// Simple webhook message
SlackNotifier::text('Hello from Laravel!')
    ->send();
```

### Basic Text Message (Web API)

```php
// Send to a specific channel using Web API
SlackNotifier::via('api')
    ->to('#general')
    ->text('Hello from Laravel!')
    ->send();

// Send to a user
SlackNotifier::via('api')
    ->to('@username')
    ->text('Hey! Check this out.')
    ->send();
```

### Ephemeral Messages (Visible Only to One User)

```php
// Send an ephemeral message (only visible to specific user)
SlackNotifier::via('api')
    ->to('#general')
    ->toUser('U1234567890')  // Slack user ID
    ->text('This message is only visible to you!')
    ->asEphemeral()
    ->send();

// Quick ephemeral message
SlackNotifier::quickEphemeralMessage(
    'You have a pending approval request',
    'U1234567890',
    '#approvals'
);

// Ephemeral block message
SlackNotifier::via('api')
    ->to('#team')
    ->toUser('U1234567890')
    ->addHeader('Private Notification')
    ->addSection('This is visible only to you')
    ->asEphemeral()
    ->send();
```

### Quick Methods

```php
// Quick simple message
SlackNotifier::quickMessage('Server is up and running!', '#monitoring');

// Quick block message with header
SlackNotifier::quickBlockMessage(
    'Deployment Complete',
    'Your application has been successfully deployed to production.',
    '#deployments'
);
```

### Rich Block Messages

```php
use HKWise\LaravelSlackNotifier\Facades\SlackNotifier;

SlackNotifier::via('api')
    ->to('#general')
    ->addHeader('📊 Daily Report')
    ->addDivider()
    ->addSection('*Sales Overview*')
    ->addSection('Total Sales: $12,450\nNew Customers: 23\nOrders: 145')
    ->addDivider()
    ->addContext([
        [
            'type' => 'mrkdwn',
            'text' => 'Generated on: ' . now()->format('M d, Y')
        ]
    ])
    ->send();
```

### Custom Block Messages

```php
SlackNotifier::blocks([
    [
        'type' => 'header',
        'text' => [
            'type' => 'plain_text',
            'text' => '🚨 System Alert'
        ]
    ],
    [
        'type' => 'section',
        'text' => [
            'type' => 'mrkdwn',
            'text' => '*High CPU Usage Detected*\nServer: web-01\nUsage: 95%'
        ]
    ],
    [
        'type' => 'section',
        'fields' => [
            [
                'type' => 'mrkdwn',
                'text' => '*Server:*\nweb-01'
            ],
            [
                'type' => 'mrkdwn',
                'text' => '*CPU:*\n95%'
            ]
        ]
    ]
])->send();
```

### Customizing Bot Appearance

```php
SlackNotifier::username('Custom Bot')
    ->iconEmoji(':fire:')
    ->text('Message with custom appearance')
    ->send();
```

### Complete Example: Error Notification

```php
try {
    // Your code here
} catch (\Exception $e) {
    SlackNotifier::via('api')
        ->to('#errors')
        ->username('Error Bot')
        ->iconEmoji(':warning:')
        ->addHeader('⚠️ Application Error')
        ->addDivider()
        ->addSection("*Error:* {$e->getMessage()}")
        ->addSection("*File:* {$e->getFile()}:{$e->getLine()}")
        ->addDivider()
        ->addContext([
            [
                'type' => 'mrkdwn',
                'text' => 'Environment: ' . app()->environment()
            ],
            [
                'type' => 'mrkdwn',
                'text' => 'Time: ' . now()->toDateTimeString()
            ]
        ])
        ->send();
}
```

### Using in Controllers

```php
namespace App\Http\Controllers;

use HKWise\LaravelSlackNotifier\Facades\SlackNotifier;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create($request->all());

        // Notify team about new order
        SlackNotifier::via('api')
            ->to('#orders')
            ->addHeader('🛍️ New Order Received')
            ->addSection("*Order ID:* #{$order->id}")
            ->addSection("*Customer:* {$order->customer_name}")
            ->addSection("*Total:* \${$order->total}")
            ->send();

        return response()->json($order);
    }
}
```

### Using in Jobs

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use HKWise\LaravelSlackNotifier\Facades\SlackNotifier;

class NotifySlack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        SlackNotifier::quickMessage('Background job completed!', '#notifications');
    }
}
```

## Testing Your Configuration

The package includes an artisan command to test your Slack configuration:

```bash
# Test webhook method (default)
php artisan slack:test

# Test Web API method
php artisan slack:test --method=api --channel=#general

# Test with a specific channel
php artisan slack:test --method=api --channel=@username
```

## Available Methods

### Configuration Methods

| Method | Description | Example |
|--------|-------------|---------|
| `via(string $method)` | Set sending method ('webhook' or 'api') | `->via('api')` |
| `to(string $channel)` | Set channel/user (API only) | `->to('#general')` |
| `toUser(string $userId)` | Set user ID for ephemeral messages | `->toUser('U1234567890')` |
| `username(string $name)` | Set bot username | `->username('Bot')` |
| `iconEmoji(string $emoji)` | Set bot icon | `->iconEmoji(':robot_face:')` |
| `asEphemeral(bool $ephemeral)` | Mark message as ephemeral | `->asEphemeral()` |

### Message Methods

| Method | Description | Example |
|--------|-------------|---------|
| `text(string $text)` | Set simple text message | `->text('Hello!')` |
| `blocks(array $blocks)` | Set custom blocks | `->blocks([...])` |
| `addBlock(array $block)` | Add a single block | `->addBlock([...])` |
| `addHeader(string $text)` | Add header block | `->addHeader('Title')` |
| `addSection(string $text, string $type)` | Add section block | `->addSection('Content')` |
| `addDivider()` | Add divider block | `->addDivider()` |
| `addContext(array $elements)` | Add context block | `->addContext([...])` |
| `addAttachment(array $attachment)` | Add attachment | `->addAttachment([...])` |

### Sending Methods

| Method | Description | Example |
|--------|-------------|---------|
| `send()` | Send the message | `->send()` |
| `sendTestMessage(?string $method, ?string $channel)` | Send test message | `->sendTestMessage('api', '#test')` |
| `quickMessage(string $text, ?string $channel)` | Quick text message | `->quickMessage('Hello', '#general')` |
| `quickBlockMessage(string $header, string $message, ?string $channel)` | Quick block message | `->quickBlockMessage('Title', 'Text', '#general')` |
| `quickEphemeralMessage(string $text, string $userId, ?string $channel)` | Quick ephemeral message | `->quickEphemeralMessage('Private', 'U123', '#general')` |

## Block Types Reference

### Header Block
```php
->addHeader('My Header')
```

### Section Block
```php
->addSection('*Bold text* and _italic text_')
->addSection('Plain text', 'plain_text')
```

### Divider Block
```php
->addDivider()
```

### Context Block
```php
->addContext([
    ['type' => 'mrkdwn', 'text' => 'Context info'],
    ['type' => 'mrkdwn', 'text' => 'More context']
])
```

### Custom Blocks
```php
->blocks([
    [
        'type' => 'section',
        'text' => [
            'type' => 'mrkdwn',
            'text' => 'Custom section'
        ],
        'accessory' => [
            'type' => 'button',
            'text' => [
                'type' => 'plain_text',
                'text' => 'Click Me'
            ],
            'url' => 'https://example.com'
        ]
    ]
])
```

For more block types and options, see the [Slack Block Kit Builder](https://api.slack.com/block-kit/building).

## Error Handling

The package throws exceptions when things go wrong. Always wrap your calls in try-catch blocks:

```php
try {
    SlackNotifier::quickMessage('Hello!');
} catch (\Exception $e) {
    Log::error('Slack notification failed: ' . $e->getMessage());
}
```

Common errors:
- `Slack webhook URL is not configured` - Add `SLACK_WEBHOOK_URL` to `.env`
- `Slack API token is not configured` - Add `SLACK_BOT_TOKEN` to `.env`
- `Channel is required for API method` - Use `->to('#channel')` when using API method
- `channel_not_found` - Bot needs to be invited to the private channel. Use `/invite @BotName` in the channel

## Disabling Notifications

You can disable notifications globally (useful for local development):

```env
SLACK_NOTIFICATIONS_ENABLED=false
```

When disabled, all `send()` calls will return success without actually sending.

## Advanced Configuration

The config file (`config/slack-notifier.php`) contains additional options:

```php
return [
    'default_method' => 'webhook',  // or 'api'
    'webhook_url' => env('SLACK_WEBHOOK_URL'),
    'api' => [
        'token' => env('SLACK_BOT_TOKEN'),
        'default_channel' => env('SLACK_DEFAULT_CHANNEL', '#general'),
    ],
    'timeout' => 10,  // HTTP request timeout in seconds
    'enabled' => true,  // Enable/disable globally
    'defaults' => [
        'username' => 'Laravel Bot',
        'icon_emoji' => ':robot_face:',
    ],
];
```

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

Developed by [Hassan Khan](https://github.com/hassankhan-wise)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you find this package helpful, please consider giving it a ⭐ on [GitHub](https://github.com/hassankhan-wise/laravel-slack-notifier)!

## Useful Links

- [Slack API Documentation](https://api.slack.com/)
- [Slack Block Kit Builder](https://api.slack.com/block-kit/building)
- [Incoming Webhooks Guide](https://api.slack.com/messaging/webhooks)
- [Web API Reference](https://api.slack.com/methods/chat.postMessage)
