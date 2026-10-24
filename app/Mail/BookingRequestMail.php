<?php

namespace App\Mail;

use App\Support\SpamGuard;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;


class BookingRequestMail extends Mailable {

    use Queueable,
        SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details) {
        $this->details = $details;
        $this->details->put('subject', $this->getSubject());

    }

    public function getSubject() {
        if ($this->details->get('request') == 'informationRequest') {
            return 'New Information Request from ' . SpamGuard::singleLine($this->details->get('firstName'), 60);
        } else if ($this->details->get('request') == 'bookingRequest') {
            return 'New Booking Request from ' . SpamGuard::singleLine($this->details->get('firstName'), 60);
        } else {
            return 'New mail from Apartments Nature website';
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject($this->getSubject())
                        ->view('emails.bookingRequest');
    }

}
