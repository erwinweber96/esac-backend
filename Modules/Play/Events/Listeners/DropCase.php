<?php

namespace Modules\Play\Events\Listeners;

use Modules\Console\Events\MatchEnded;
use Modules\Event\Entities\EventProperty;
use Modules\Play\Entities\CaseDrop;
use function Sentry\captureMessage;

class DropCase
{
    public function handle(MatchEnded $matchEnded)
    {
        captureMessage("It does drop case");
        $match = $matchEnded->match;
        $isRanked = $match->group->event->properties->filter(function (EventProperty $property) {
            return $property->key == EventProperty::RANKED_EVENT;
        });

        if (!$isRanked->count()) {
            captureMessage("It does not drop case. Not ranked.");
            return;
        }

        foreach ($match->participants as $participant) {
            $hasDropped = rand(1, CaseDrop::DROP_PROBABILITY);
            if ($hasDropped == CaseDrop::DROP_PROBABILITY) {
                $caseDrop = new CaseDrop();
                $caseDrop->userId = $participant->userId;
                $caseDrop->seen = false;
                $caseDrop->caseId = CaseDrop::DOODLE_CASE_ID;
                $caseDrop->save();
            }
        }
    }
}
