<?php

namespace HKWise\LaravelSlackNotifier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier via(string $method)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier to(string $channel)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier toUser(string $userId)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier username(string $username)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier iconEmoji(string $emoji)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier text(string $text)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier addBlock(array $block)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier blocks(array $blocks)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier addSection(string $text, string $type = 'mrkdwn')
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier addHeader(string $text)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier addDivider()
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier addContext(array $elements)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier addAttachment(array $attachment)
 * @method static \HKWise\LaravelSlackNotifier\SlackNotifier asEphemeral(bool $ephemeral = true)
 * @method static array send()
 * @method static array sendTestMessage(?string $method = null, ?string $channel = null)
 * @method static array quickMessage(string $text, ?string $channel = null)
 * @method static array quickBlockMessage(string $header, string $message, ?string $channel = null)
 * @method static array quickEphemeralMessage(string $text, string $userId, ?string $channel = null)
 *
 * @see \HKWise\LaravelSlackNotifier\SlackNotifier
 */
class SlackNotifier extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'slack-notifier';
    }
}
