<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customerName;
    public $pickupTime;
    public $pickupLocation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customerName, $pickupTime, $pickupLocation)
    {
        $this->customerName = $customerName;
        $this->pickupTime = $pickupTime;
        $this->pickupLocation = $pickupLocation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.booking_confirmation')
                    ->subject('Click Excursions Confirmation')
                    ->with([
                        'customerName' => $this->customerName,
                        'pickupTime' => $this->pickupTime,
                        'pickupLocation' => $this->pickupLocation,
                    ]);
    }
}
