<?php

namespace HKWise\LaravelSlackNotifier;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SlackNotifier
{
    /**
     * The HTTP client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * The method to use for sending messages (webhook or api).
     *
     * @var string
     */
    protected $method;

    /**
     * The channel to send the message to (for API method).
     *
     * @var string|null
     */
    protected $channel;

    /**
     * The username for the bot.
     *
     * @var string|null
     */
    protected $username;

    /**
     * The icon emoji for the bot.
     *
     * @var string|null
     */
    protected $iconEmoji;

    /**
     * The message text.
     *
     * @var string|null
     */
    protected $text;

    /**
     * The message blocks.
     *
     * @var array
     */
    protected $blocks = [];

    /**
     * Additional attachments.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * The user ID for ephemeral messages.
     *
     * @var string|null
     */
    protected $user;

    /**
     * Whether to send as ephemeral message.
     *
     * @var bool
     */
    protected $isEphemeral = false;

    /**
     * Create a new SlackNotifier instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('slack-notifier.timeout', 10),
        ]);

        $this->method = config('slack-notifier.default_method', 'webhook');
        $this->channel = config('slack-notifier.api.default_channel');
        $this->username = config('slack-notifier.defaults.username');
        $this->iconEmoji = config('slack-notifier.defaults.icon_emoji');
    }

    /**
     * Set the notification method (webhook or api).
     *
     * @param string $method
     * @return $this
     */
    public function via(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set the channel to send the message to.
     *
     * @param string $channel
     * @return $this
     */
    public function to(string $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Set the username for the bot.
     *
     * @param string $username
     * @return $this
     */
    public function username(string $username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the icon emoji for the bot.
     *
     * @param string $emoji
     * @return $this
     */
    public function iconEmoji(string $emoji)
    {
        $this->iconEmoji = $emoji;
        return $this;
    }

    /**
     * Set a simple text message.
     *
     * @param string $text
     * @return $this
     */
    public function text(string $text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Add a block to the message.
     *
     * @param array $block
     * @return $this
     */
    public function addBlock(array $block)
    {
        $this->blocks[] = $block;
        return $this;
    }

    /**
     * Set multiple blocks at once.
     *
     * @param array $blocks
     * @return $this
     */
    public function blocks(array $blocks)
    {
        $this->blocks = $blocks;
        return $this;
    }

    /**
     * Add a section block with text.
     *
     * @param string $text
     * @param string $type (plain_text or mrkdwn)
     * @return $this
     */
    public function addSection(string $text, string $type = 'mrkdwn')
    {
        $this->blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => $type,
                'text' => $text,
            ],
        ];
        return $this;
    }

    /**
     * Add a header block.
     *
     * @param string $text
     * @return $this
     */
    public function addHeader(string $text)
    {
        $this->blocks[] = [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => $text,
            ],
        ];
        return $this;
    }

    /**
     * Add a divider block.
     *
     * @return $this
     */
    public function addDivider()
    {
        $this->blocks[] = [
            'type' => 'divider',
        ];
        return $this;
    }

    /**
     * Add a context block.
     *
     * @param array $elements
     * @return $this
     */
    public function addContext(array $elements)
    {
        $this->blocks[] = [
            'type' => 'context',
            'elements' => $elements,
        ];
        return $this;
    }

    /**
     * Add an attachment.
     *
     * @param array $attachment
     * @return $this
     */
    public function addAttachment(array $attachment)
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * Set the user ID for ephemeral messages (API only).
     *
     * @param string $userId
     * @return $this
     */
    public function toUser(string $userId)
    {
        $this->user = $userId;
        return $this;
    }

    /**
     * Mark this message as ephemeral (visible only to one user).
     * Requires both channel and user to be set.
     *
     * @param bool $ephemeral
     * @return $this
     */
    public function asEphemeral(bool $ephemeral = true)
    {
        $this->isEphemeral = $ephemeral;
        return $this;
    }

    /**
     * Send the message using the configured method.
     *
     * @return array
     * @throws \Exception
     */
    public function send()
    {
        if (!config('slack-notifier.enabled', true)) {
            Log::info('Slack notifications are disabled');
            return [
                'ok' => true,
                'message' => 'Notifications disabled',
            ];
        }

        // Ephemeral messages must use API method
        if ($this->isEphemeral) {
            return $this->sendEphemeralViaApi();
        }

        return $this->method === 'api' ? $this->sendViaApi() : $this->sendViaWebhook();
    }

    /**
     * Send a message via webhook.
     *
     * @return array
     * @throws \Exception
     */
    protected function sendViaWebhook()
    {
        $webhookUrl = config('slack-notifier.webhook_url');

        if (empty($webhookUrl)) {
            throw new \Exception('Slack webhook URL is not configured');
        }

        $payload = $this->buildPayload();

        try {
            $response = $this->client->post($webhookUrl, [
                'json' => $payload,
            ]);

            $this->reset();

            return [
                'ok' => $response->getStatusCode() === 200,
                'response' => $response->getBody()->getContents(),
            ];
        } catch (GuzzleException $e) {
            Log::error('Slack webhook error: ' . $e->getMessage());
            throw new \Exception('Failed to send Slack message via webhook: ' . $e->getMessage());
        }
    }

    /**
     * Send a message via Web API (chat.postMessage).
     *
     * @return array
     * @throws \Exception
     */
    protected function sendViaApi()
    {
        $token = config('slack-notifier.api.token');

        if (empty($token)) {
            throw new \Exception('Slack API token is not configured');
        }

        if (empty($this->channel)) {
            throw new \Exception('Channel is required for API method');
        }

        $payload = $this->buildPayload();
        $payload['channel'] = $this->channel;

        try {
            $response = $this->client->post('https://slack.com/api/chat.postMessage', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $this->reset();

            $body = json_decode($response->getBody()->getContents(), true);

            if (!$body['ok']) {
                throw new \Exception('Slack API error: ' . ($body['error'] ?? 'Unknown error'));
            }

            return $body;
        } catch (GuzzleException $e) {
            Log::error('Slack API error: ' . $e->getMessage());
            throw new \Exception('Failed to send Slack message via API: ' . $e->getMessage());
        }
    }

    /**
     * Send an ephemeral message via Web API (chat.postEphemeral).
     * Ephemeral messages are visible only to the specified user.
     *
     * @return array
     * @throws \Exception
     */
    protected function sendEphemeralViaApi()
    {
        $token = config('slack-notifier.api.token');

        if (empty($token)) {
            throw new \Exception('Slack API token is not configured');
        }

        if (empty($this->channel)) {
            throw new \Exception('Channel is required for ephemeral messages');
        }

        if (empty($this->user)) {
            throw new \Exception('User ID is required for ephemeral messages. Use toUser() method.');
        }

        $payload = $this->buildPayload();
        $payload['channel'] = $this->channel;
        $payload['user'] = $this->user;

        try {
            $response = $this->client->post('https://slack.com/api/chat.postEphemeral', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $this->reset();

            $body = json_decode($response->getBody()->getContents(), true);

            if (!$body['ok']) {
                throw new \Exception('Slack API error: ' . ($body['error'] ?? 'Unknown error'));
            }

            return $body;
        } catch (GuzzleException $e) {
            Log::error('Slack API error (ephemeral): ' . $e->getMessage());
            throw new \Exception('Failed to send ephemeral Slack message: ' . $e->getMessage());
        }
    }

    /**
     * Build the payload for the Slack message.
     *
     * @return array
     */
    protected function buildPayload()
    {
        $payload = [];

        if ($this->text) {
            $payload['text'] = $this->text;
        }

        if (!empty($this->blocks)) {
            $payload['blocks'] = $this->blocks;
        }

        if (!empty($this->attachments)) {
            $payload['attachments'] = $this->attachments;
        }

        if ($this->username) {
            $payload['username'] = $this->username;
        }

        if ($this->iconEmoji) {
            $payload['icon_emoji'] = $this->iconEmoji;
        }

        return $payload;
    }

    /**
     * Reset the instance state after sending.
     *
     * @return void
     */
    protected function reset()
    {
        $this->text = null;
        $this->blocks = [];
        $this->attachments = [];
        $this->channel = config('slack-notifier.api.default_channel');
        $this->username = config('slack-notifier.defaults.username');
        $this->iconEmoji = config('slack-notifier.defaults.icon_emoji');
        $this->user = null;
        $this->isEphemeral = false;
    }

    /**
     * Send a test message to verify the configuration.
     *
     * @param string|null $method
     * @param string|null $channel
     * @return array
     */
    public function sendTestMessage(?string $method = null, ?string $channel = null)
    {
        if ($method) {
            $this->via($method);
        }

        if ($channel) {
            $this->to($channel);
        }

        return $this->text('🎉 Test message from Laravel Slack Notifier! Your configuration is working correctly.')
            ->addDivider()
            ->addSection('*This is a test notification*')
            ->addContext([
                [
                    'type' => 'mrkdwn',
                    'text' => 'Sent at: ' . now()->format('Y-m-d H:i:s'),
                ],
            ])
            ->send();
    }

    /**
     * Create a quick simple message and send it.
     *
     * @param string $text
     * @param string|null $channel
     * @return array
     */
    public function quickMessage(string $text, ?string $channel = null)
    {
        if ($channel) {
            $this->to($channel);
        }

        return $this->text($text)->send();
    }

    /**
     * Create a quick block message with header and send it.
     *
     * @param string $header
     * @param string $message
     * @param string|null $channel
     * @return array
     */
    public function quickBlockMessage(string $header, string $message, ?string $channel = null)
    {
        if ($channel) {
            $this->to($channel);
        }

        return $this->addHeader($header)
            ->addDivider()
            ->addSection($message)
            ->send();
    }

    /**
     * Send a quick ephemeral message (visible only to one user).
     *
     * @param string $text
     * @param string $userId User ID (e.g., 'U1234567890')
     * @param string|null $channel Channel where the message appears
     * @return array
     */
    public function quickEphemeralMessage(string $text, string $userId, ?string $channel = null)
    {
        if ($channel) {
            $this->to($channel);
        }

        return $this->via('api')
            ->toUser($userId)
            ->text($text)
            ->asEphemeral()
            ->send();
    }
}
