<?php


namespace Modules\Event\Builders;


use App\Model\Builder;

class EventFaqBuilder implements Builder
{
    /** @var array $faq */
    private $faq;

    public function prepare(): Builder
    {
        $this->faq = [];
        return $this;
    }

    public function build()
    {
        return $this->faq;
    }

    public function setEventId(int $eventId): self
    {
        $this->faq['event_id'] = $eventId;
        return $this;
    }

    public function setQuestion(string $question): self
    {
        $this->faq['question'] = $question;
        return $this;
    }

    public function setAnswer(string $answer): self
    {
        $this->faq['answer'] = $answer;
        return $this;
    }
}
