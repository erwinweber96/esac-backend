<?php


namespace Modules\User\Events;


use Illuminate\Mail\Mailable;
use Modules\User\Entities\ResetToken;

class ResetTokenGenerated extends Mailable
{
    /** @var ResetToken $resetToken */
    public $resetToken;

    public $subject = "Reset Password";

    /**
     * ResetTokenGenerated constructor.
     * @param ResetToken $resetToken
     */
    public function __construct(ResetToken $resetToken)
    {
        $this->resetToken = $resetToken;
    }

    public function build()
    {
        return $this
            ->from("contact@esac.gg")
            ->view("user::mails.reset", [
                "resetToken" => $this->resetToken
            ]);
    }
}
