<?php


namespace Modules\Console\Mails;


use Illuminate\Mail\Mailable;

class BecomeATrackmaniaMaster extends Mailable
{
    public function build()
    {
        return $this
            ->from("contact@esac.gg")
            ->view("console::mails.become_a_trackmania_master");
    }
}
