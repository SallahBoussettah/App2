<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Reservation;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyClients extends Command
{
    protected $signature = 'notify:clients';
    protected $description = 'Send email notifications to clients for upcoming reservations';

    public function handle()
    {
        $now = Carbon::now();

        $reservations = Reservation::where('active_auto_notify', true)->get();

        foreach ($reservations as $reservation) {
            // Combine date and time fields
            
            try {
                
                $reservationDateTime = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $reservation->date_resa . ' ' . $reservation->hour_start
                );
                 Log::info('Email notify...'.$reservationDateTime);
                
            } catch (\Exception $e) {
                Log::error("Invalid date/time format for reservation ID: {$reservation->id}");
                Log::error("Date: {$reservation->date_resa}, Time: {$reservation->hour_start}");
                continue; // Skip this reservation and proceed with others
            }


            $hoursDifference = $now->diffInHours($reservationDateTime, false);
            Log::info('Email notify...'.$reservationDateTime);
            // Send 24-hour notification if not already sent
            if ($hoursDifference === 24 && !$reservation->notified_24h) {
                $this->sendEmail($reservation);
                $reservation->notified_24h = true;
                $reservation->save();
                $this->info("24-hour email sent for reservation ID: {$reservation->id}");
            }

            // Send 12-hour notification if not already sent
            if ($hoursDifference === 12 && !$reservation->notified_12h) {
                $this->sendEmail($reservation);
                $reservation->notified_12h = true;
                $reservation->save();
                $this->info("12-hour email sent for reservation ID: {$reservation->id}");
            }
        }

        return 0;
    }

    private function sendEmail($reservation)
    {
        try {
            $customerName = $reservation->client_nom;
            $pickupTime = $reservation->hour_start;
            $pickupLocation = $reservation->hotel_adresse;
            $maps = $reservation->maps;
            $pick_up = $reservation->pick_up;
            $contactNumber = '+212 666 887333';

            Mail::send('emails.booking_confirmation', [
                'customerName' => $customerName,
                'pickupTime' => $pickupTime,
                'pickupLocation' => $pickupLocation,
                'pick_up' => $pick_up,
                'maps' => $maps,
                'contactNumber' => $contactNumber,
            ], function ($message) use ($reservation) {
                $message->to($reservation->client_email);
                $message->subject('Travel Confirmation (Reminder)');
            });

        } catch (\Exception $e) {
            $this->error("Failed to send email for reservation ID: {$reservation->id}. Error: {$e->getMessage()}");
        }
    }
}
