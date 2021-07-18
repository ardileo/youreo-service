<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendInvitationNotif extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct($details)
    {
        $this->details = $details;
        $this->details['subject'] = isset($details['subject']) ? $details['subject'] : null;
    }

    public function build()
    {
        return $this->from('mail@mail.com', 'Youreo.ID (no-reply)')
            ->view('emails.invit-notif')
            ->subject($this->details['subject'])
            ->with($this->details);
    }
}
