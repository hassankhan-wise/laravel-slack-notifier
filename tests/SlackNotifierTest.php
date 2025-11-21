<?php

namespace HKWise\LaravelSlackNotifier\Tests;

use Orchestra\Testbench\TestCase;
use HKWise\LaravelSlackNotifier\SlackNotifier;
use HKWise\LaravelSlackNotifier\SlackNotifierServiceProvider;
use HKWise\LaravelSlackNotifier\Facades\SlackNotifier as SlackNotifierFacade;

class SlackNotifierTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [SlackNotifierServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'SlackNotifier' => SlackNotifierFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup test environment
        $app['config']->set('slack-notifier.webhook_url', 'https://hooks.slack.com/test');
        $app['config']->set('slack-notifier.api.token', 'xoxb-test-token');
        $app['config']->set('slack-notifier.api.default_channel', '#test');
        $app['config']->set('slack-notifier.enabled', false); // Disable actual sending in tests
    }

    /** @test */
    public function it_can_create_slack_notifier_instance()
    {
        $notifier = new SlackNotifier();
        $this->assertInstanceOf(SlackNotifier::class, $notifier);
    }

    /** @test */
    public function it_can_set_text_message()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->text('Test message');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_set_channel()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->to('#general');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_set_username()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->username('Test Bot');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_set_icon_emoji()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->iconEmoji(':robot_face:');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_add_header_block()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->addHeader('Test Header');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_add_section_block()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->addSection('Test section');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_add_divider_block()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->addDivider();

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_chain_methods()
    {
        $notifier = new SlackNotifier();
        $result = $notifier
            ->to('#test')
            ->username('Bot')
            ->text('Message')
            ->addHeader('Header')
            ->addSection('Section');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_set_via_method()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->via('api');

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_set_blocks()
    {
        $notifier = new SlackNotifier();
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => 'Test'
                ]
            ]
        ];

        $result = $notifier->blocks($blocks);
        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_add_context_block()
    {
        $notifier = new SlackNotifier();
        $result = $notifier->addContext([
            [
                'type' => 'mrkdwn',
                'text' => 'Context text'
            ]
        ]);

        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_returns_success_when_notifications_disabled()
    {
        config(['slack-notifier.enabled' => false]);

        $notifier = new SlackNotifier();
        $result = $notifier->text('Test')->send();

        $this->assertTrue($result['ok']);
        $this->assertEquals('Notifications disabled', $result['message']);
    }

    /** @test */
    public function facade_works()
    {
        $result = SlackNotifierFacade::text('Test message');
        $this->assertInstanceOf(SlackNotifier::class, $result);
    }

    /** @test */
    public function it_can_use_quick_message_method()
    {
        config(['slack-notifier.enabled' => false]);

        $result = SlackNotifierFacade::quickMessage('Quick test', '#test');

        $this->assertIsArray($result);
        $this->assertTrue($result['ok']);
    }

    /** @test */
    public function it_can_use_quick_block_message_method()
    {
        config(['slack-notifier.enabled' => false]);

        $result = SlackNotifierFacade::quickBlockMessage('Header', 'Message', '#test');

        $this->assertIsArray($result);
        $this->assertTrue($result['ok']);
    }
}
