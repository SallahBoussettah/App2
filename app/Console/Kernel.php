<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\FetchEmails::class,
        Commands\RefreshGmailToken::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        
        $schedule->command('emails:fetch')->everyFiveMinutes()->before(function () {
            Log::info('Email fetch job starting...');
        })->after(function () {
            Log::info('Email fetch job completed!');
        });
        
        $schedule->command('gmail:refresh-token')->everyThirtyMinutes()->before(function () {
            Log::info('Email refresh Toekn job starting...');
        })->after(function () {
            Log::info('Email refresh Toekn job completed!');
        });
        
        $schedule->command('notify:clients')->everyFifteenMinutes()->before(function () {
            Log::info('notify Email job starting...');
        })->after(function () {
            Log::info('notify Email job completed!');
        });
        


    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
