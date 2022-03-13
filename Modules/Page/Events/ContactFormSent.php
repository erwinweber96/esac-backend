<?php


namespace Modules\Page\Events;


use Illuminate\Mail\Mailable;

/**
 * Class ContactFormSent
 * @package Modules\Page\Events
 */
class ContactFormSent extends Mailable
{
    private $mailSubject;
    private $firstName;
    private $lastName;
    private $email;
    private $text;

    /**
     * ContactFormSent constructor.
     * @param $mailSubject
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $text
     */
    public function __construct(
        $mailSubject, $firstName, $lastName, $email, $text)
    {
        $this->mailSubject = $mailSubject;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->text = $text;
    }


    public function build()
    {
        return $this
            ->from("contact@esac.gg")
            ->subject($this->mailSubject)
            ->html($this->firstName . " " . $this->lastName . " " .
                $this->email . " | " . $this->text);
    }
}
