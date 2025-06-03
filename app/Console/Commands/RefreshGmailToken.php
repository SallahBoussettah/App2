<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LaravelGmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class RefreshGmailToken extends Command
{
    protected $signature = 'gmail:refresh-token';
    protected $description = 'Refresh Gmail API token if it is close to expiring';

    public function handle()
    {
        $token = json_decode(Storage::disk('local')->get('gmail/tokens/gmail-json.json'), true);

        if (isset($token['created']) && isset($token['expires_in'])) {
            $expirationTime = $token['created'] + $token['expires_in'];
            $timeRemaining = $expirationTime - time();

            if ($timeRemaining < 300) { // Less than 5 minutes remaining
                try {
                    LaravelGmail::makeToken(); // Refresh token with the refresh token
                    Log::info('Token refreshed successfully by cron.');
                    $this->info('Token refreshed successfully.');
                } catch (\Exception $e) {
                    Log::error('Failed to refresh Gmail token: ' . $e->getMessage());
                    $this->sendFailureNotification('Failed to refresh Gmail token: ' . $e->getMessage());
                    $this->error('Failed to refresh token.');
                }
            } else {
             
                $this->info('Token is still valid, no need to refresh.');
            }
        } else {
            
            Log::error('Token data is missing required keys: `created` or `expires_in`.');
            $this->sendFailureNotification('Token data is missing required keys: `created` or `expires_in`.');
            $this->error('Token data is missing required keys: `created` or `expires_in`.');
            return;
        }
    }
    
    private function sendFailureNotification($errorMessage)
    {
        $toEmail = 'web@eureka-creation.ma'; // Email to send notifications to

        Mail::raw("An error occurred while trying to refresh the Gmail token:\n\n{$errorMessage}", function ($message) use ($toEmail) {
            $message->to($toEmail)
                    ->subject('Gmail Token Refresh Failure (Click App)')
                    ->from('noreply@clickexcursions.com');
        });
    }
}
