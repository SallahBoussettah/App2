<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EmailsController;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new emails and store bookings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Call the getemails function from the EmailsController
        $controller = new EmailsController();
        $controller->getemails();
        $controller->getemails_new();

        $this->info('Emails fetched successfully.');
        return 0;
    }
}
