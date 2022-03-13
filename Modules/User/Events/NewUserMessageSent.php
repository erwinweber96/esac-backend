<?php


namespace Modules\User\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\User\Entities\UserMessage;

class NewUserMessageSent implements ShouldBroadcastNow
{
    /** @var UserMessage $userMessage */
    public $userMessage;

    /**
     * NewUserMessageSent constructor.
     * @param UserMessage $userMessage
     */
    public function __construct(UserMessage $userMessage)
    {
        $this->userMessage = $userMessage;
    }

    public function broadcastOn()
    {
        return new Channel($this->userMessage->channel);
    }

    public function broadcastAs()
    {
        return "created";
    }
}
