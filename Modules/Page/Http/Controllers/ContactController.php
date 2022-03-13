<?php


namespace Modules\Page\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Page\Events\ContactFormSent;

class ContactController
{
    public function contact(Request $request)
    {
        $firstName = $request->input("firstName");
        $lastName = $request->input("lastName");
        $email = $request->input("email");
        $subject = $request->input("subject");
        $text = $request->input("text");

        $mail = new ContactFormSent($subject, $firstName, $lastName, $email, $text);
        Mail::to("contact@esac.gg")->send($mail);
    }
}
