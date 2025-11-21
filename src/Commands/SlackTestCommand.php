<?php

namespace HKWise\LaravelSlackNotifier\Commands;

use Illuminate\Console\Command;
use HKWise\LaravelSlackNotifier\Facades\SlackNotifier;

class SlackTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:test
                            {--method=webhook : The method to use (webhook or api)}
                            {--channel= : The channel to send to (required for api method)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test message to Slack to verify your configuration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $method = $this->option('method');
        $channel = $this->option('channel');

        $this->info('Sending test message to Slack...');
        $this->info('Method: ' . $method);

        if ($channel) {
            $this->info('Channel: ' . $channel);
        }

        try {
            $response = SlackNotifier::sendTestMessage($method, $channel);

            if ($response['ok']) {
                $this->info('✓ Test message sent successfully!');
                $this->line('Response: ' . json_encode($response, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            } else {
                $this->error('✗ Failed to send test message');
                $this->line('Response: ' . json_encode($response, JSON_PRETTY_PRINT));
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
