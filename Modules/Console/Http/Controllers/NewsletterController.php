<?php


namespace Modules\Console\Http\Controllers;


use Illuminate\Http\Response;
use Modules\Console\Mails\BecomeATrackmaniaMaster;
use Illuminate\Support\Facades\Mail;
use Modules\User\Entities\User;
use Modules\User\Events\ResetTokenGenerated;

class NewsletterController
{
    public function send()
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id != 15) {
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        }

        $email = "???"; //TODO:
        Mail::to($email)->send(new BecomeATrackmaniaMaster());

        return ["success" => true];
    }
}
