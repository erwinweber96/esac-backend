<?php


namespace Modules\Play\Console;


use Illuminate\Console\Command;
use Modules\Console\Services\DedicatedControllerService;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\EventProperty;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\FormatType;
use Modules\Group\Entities\Group;
use Modules\Match\Entities\MatchModel;
use Modules\Match\Entities\MatchResult;
use Modules\Play\Jobs\CachePlayEventsJob;
use Modules\Play\Services\HourlyEventService;
use Modules\User\Entities\CoinTransaction;

/**
 * Class PrepareHourlyEventGroups
 * @package Modules\Play\Console
 */
class PrepareHourlyEventGroups extends Command
{
    const COMMAND = "hourly_events:prepare_groups";

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = self::COMMAND;

    /** @var DedicatedControllerService $dedicatedControllerService */
    private $dedicatedControllerService;

    /**
     * PrepareHourlyEventGroups constructor.
     * @param DedicatedControllerService $dedicatedControllerService
     */
    public function __construct(DedicatedControllerService $dedicatedControllerService)
    {
        parent::__construct();
        $this->dedicatedControllerService = $dedicatedControllerService;
    }

    public function handle()
    {
        $liveHourlies = Event::where("status_id", Event::STATUS_LIVE)
            ->where("page_id", HourlyEventService::PAGE_ID)
            ->get();

        $customShowdownProps = EventProperty::where("key", EventProperty::CUSTOM_SHOWDOWN)->get();
        $customShowdownIds = $customShowdownProps->map(function (EventProperty $eventProperty) {
            return $eventProperty->eventId;
        });
        $customShowdowns = Event::whereIn("id", $customShowdownIds->toArray())
            ->where("status_id", Event::STATUS_LIVE)
            ->get();

        $liveEvents = $liveHourlies->merge($customShowdowns);

        if (!$liveEvents->count()) {
            return;
        }

        /** @var Event $liveEvent */
        foreach ($liveEvents as $liveEvent) {
            //check 1v1
            $oneVsOneGroup = $liveEvent->groups->filter(function (Group $group) {
                if ($group->name == HourlyEventService::ONE_VS_ONE_GROUP_NAME) {
                    return true;
                }

                return false;
            });

            if ($oneVsOneGroup->count()) {
                /** @var Group $oneVsOneGroup */
                $oneVsOneGroup = $oneVsOneGroup->first();

                if ($oneVsOneGroup->matches->first()->statusId == MatchModel::STATUS_ENDED) {
                    $this->giveOutCoins($oneVsOneGroup->matches[0]);
                    $liveEvent->statusId = Event::STATUS_ENDED;
                    $liveEvent->save();
                    continue;
                }
            }

            //Checking if finals have ended
            $finalGroup = $liveEvent->groups->filter(function (Group $group) {
                if ($group->name == HourlyEventService::FINAL_GROUP_NAME) {
                    return true;
                }

                return false;
            });

            if ($finalGroup->count()) {
                /** @var Group $final */
                $final = $finalGroup->first();

                $hasEnded = $final->matches->filter(function (MatchModel $match) {
                    if ($match->statusId == MatchModel::STATUS_ENDED) {
                        return true;
                    }

                    return false;
                });

                if ($hasEnded->count()) {
                    //Event is over
                    $this->giveOutCoins($final->matches[0]);
                    $liveEvent->statusId = Event::STATUS_ENDED;
                    $liveEvent->save();
                    continue;
                }
            }

            //Checking if semi finals have ended
            $semiFinalsGroup = $liveEvent->groups->filter(function (Group $group) {
                if ($group->name == HourlyEventService::SEMI_FINALS_GROUP_NAME) {
                    return true;
                }

                return false;
            });

            if ($semiFinalsGroup->count()) {
                /** @var Group $semiFinals */
                $semiFinals = $semiFinalsGroup->first();

                $hasEnded = $semiFinals->matches->filter(function (MatchModel $match) {
                    if ($match->statusId == MatchModel::STATUS_ENDED) {
                        return true;
                    }

                    return false;
                });

                if ($hasEnded->count() == $semiFinals->matches->count()) {
                    //advance semis to final
                    $winners1 = $this->getMatchWinners($semiFinals->matches[0]);
                    $winners2 = $this->getMatchWinners($semiFinals->matches[1]);

                    /** @var Group $final */
                    $final = $finalGroup->first();
                    $finalMatch = $final->matches[0];

                    if ($finalMatch->statusId != MatchModel::STATUS_UPCOMING) {
                        continue;
                    }

                    $final->participants()->sync([
                        $winners1[0], $winners1[1], $winners2[0], $winners2[1]
                    ]);
                    $final->matches[0]->participants()->sync([
                        $winners1[0], $winners1[1], $winners2[0], $winners2[1]
                    ]);

                    try {
                        $this->dedicatedControllerService->startMatch($final->matches[0]->id);
                    } catch (\Throwable $exception) {
                        \Sentry\captureException($exception);
                    }

                    continue;
                }
            }

            //checking if quarters have ended
            $quarterFinalsGroup = $liveEvent->groups->filter(function (Group $group) {
                if ($group->name == HourlyEventService::QUARTER_FINALS_GROUP_NAME) {
                    return true;
                }

                return false;
            });

            if ($quarterFinalsGroup->count()) {
                /** @var Group $quarterFinalsGroup */
                $quarterFinalsGroup = $quarterFinalsGroup->first();

                $hasEnded = $quarterFinalsGroup->matches->filter(function (MatchModel $match) {
                    if ($match->statusId == MatchModel::STATUS_ENDED) {
                        return true;
                    }

                    return false;
                });

                if ($hasEnded->count() == $quarterFinalsGroup->matches->count()) {
                    $semiFinal1 = $semiFinals->matches[0];
                    $semiFinal2 = $semiFinals->matches[0];

                    if ($semiFinal1->statusId !== MatchModel::STATUS_UPCOMING &&
                        $semiFinal2->statusId !== MatchModel::STATUS_UPCOMING)
                    {
                        continue;
                    }

                    //advance quarters to semis
                    $winners1 = $this->getMatchWinners($quarterFinalsGroup->matches[0]);
                    $winners2 = $this->getMatchWinners($quarterFinalsGroup->matches[1]);
                    $winners3 = $this->getMatchWinners($quarterFinalsGroup->matches[2]);
                    $winners4 = $this->getMatchWinners($quarterFinalsGroup->matches[3]);

                    $allSemiPlayers = [
                        $winners1[0], $winners2[0], $winners3[1], $winners4[1],
                        $winners1[1], $winners2[1], $winners3[0], $winners4[0]
                    ];

                    $semi1Players = [
                        $winners1[0], $winners2[0], $winners3[1], $winners4[1]
                    ];

                    $semi2Players = [
                        $winners1[1], $winners2[1], $winners3[0], $winners4[0]
                    ];

                    $semiFinals->participants()->sync($allSemiPlayers);
                    $semiFinals->matches[0]->participants()->sync($semi1Players);
                    $semiFinals->matches[1]->participants()->sync($semi2Players);

                    try {
                        $this->dedicatedControllerService->startMatch($semiFinals->matches[0]->id);
                    } catch (\Throwable $exception) {
                        \Sentry\captureException($exception);
                    }

                    try {
                        $this->dedicatedControllerService->startMatch($semiFinals->matches[1]->id);
                    } catch (\Throwable $exception) {
                        \Sentry\captureException($exception);
                    }

                    continue;
                }
            }

            $timeAttackGroup = $liveEvent->groups->filter(function (Group $group) {
                if ($group->name == HourlyEventService::TIME_ATTACK_QUALIFICATION_GROUP_NAME) {
                    return true;
                }

                return false;
            });

            if ($timeAttackGroup->count()) {
                /** @var Group $timeAttackGroup */
                $timeAttackGroup = $timeAttackGroup->first();

                $hasEnded = $timeAttackGroup->matches->filter(function (MatchModel $match) {
                   if ($match->statusId == MatchModel::STATUS_ENDED) {
                       return true;
                   }

                   return false;
                });

                if ($hasEnded->count()) {
                    switch ($liveEvent->groups->count())
                    {
                        case 2:
                            //advance to final (top 4)
                            $qualified = $this->getQualiWinners($timeAttackGroup->matches->first(), 4);

                            /** @var Group $final */
                            $final = $finalGroup->first();
                            $final->participants()->sync($qualified);

                            /** @var MatchModel $finalMatch */
                            $finalMatch = $final->matches->first();

                            if ($finalMatch->statusId !== MatchModel::STATUS_UPCOMING) {
                                break;
                            }

                            $finalMatch->participants()->sync($qualified);

                            try {
                                $this->dedicatedControllerService->startMatch($finalMatch->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }

                            break;
                        case 3:
                            //advance to semi-finals (top 8)
                            $qualified = $this->getQualiWinners($timeAttackGroup->matches->first(), 8);

                            /** @var Group $semiFinals */
                            $semiFinals = $semiFinalsGroup->first();

                            $semiFinal1 = $semiFinals->matches[0];
                            $semiFinal2 = $semiFinals->matches[1];

                            if ($semiFinal1->statusId !== MatchModel::STATUS_UPCOMING &&
                                $semiFinal2->statusId !== MatchModel::STATUS_UPCOMING)
                            {
                                break;
                            }

                            $semiFinals->participants()->sync($qualified);

                            shuffle($qualified);
                            $chunks = array_chunk($qualified, ceil(count($qualified) / 2));
                            $semiFinals->matches[0]->participants()->sync($chunks[0]);
                            $semiFinals->matches[1]->participants()->sync($chunks[1]);

                            try {
                                $this->dedicatedControllerService->startMatch($semiFinals->matches[0]->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }
                            try {
                                $this->dedicatedControllerService->startMatch($semiFinals->matches[1]->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }

                            break;
                        case 4:
                            //advance to quarter-finals (top 16)
                            $qualified = $this->getQualiWinners($timeAttackGroup->matches->first(), 16);

                            /** @var Group $quarterFinals */
                            $quarterFinals = $quarterFinalsGroup;

                            $quarterFinal1 = $quarterFinals->matches[0];
                            $quarterFinal2 = $quarterFinals->matches[1];
                            $quarterFinal3 = $quarterFinals->matches[2];
                            $quarterFinal4 = $quarterFinals->matches[3];

                            if ($quarterFinal1->statusId !== MatchModel::STATUS_UPCOMING &&
                                $quarterFinal2->statusId !== MatchModel::STATUS_UPCOMING &&
                                $quarterFinal3->statusId !== MatchModel::STATUS_UPCOMING &&
                                $quarterFinal4->statusId !== MatchModel::STATUS_UPCOMING)
                            {
                                break;
                            }

                            $quarterFinals->participants()->sync($qualified);
                            shuffle($qualified);
                            $chunks = array_chunk($qualified, ceil(count($qualified) / 4));
                            $quarterFinals->matches[0]->participants()->sync($chunks[0]);
                            $quarterFinals->matches[1]->participants()->sync($chunks[1]);
                            $quarterFinals->matches[2]->participants()->sync($chunks[2]);
                            $quarterFinals->matches[3]->participants()->sync($chunks[3]);

                            try {
                                $this->dedicatedControllerService->startMatch($quarterFinals->matches[0]->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }

                            try {
                                $this->dedicatedControllerService->startMatch($quarterFinals->matches[1]->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }

                            try {
                                $this->dedicatedControllerService->startMatch($quarterFinals->matches[2]->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }

                            try {
                                $this->dedicatedControllerService->startMatch($quarterFinals->matches[3]->id);
                            } catch (\Throwable $exception) {
                                \Sentry\captureException($exception);
                            }

                            break;
                    }
                }
            }
        }

        CachePlayEventsJob::dispatch();
    }

    private function getMatchWinners(MatchModel $match)
    {
        $results = collect($match->totalMatchResults);
        $results = $results->sortByDesc('result', SORT_NATURAL);
        $winners = $results->take(2);
        $winners = $winners->map(function (?MatchResult $result, $participantId) {
            return $participantId;
        });
        $arrayWithObject = array_values((array)$winners);
        return array_values((array)$arrayWithObject[0]);
    }

    private function getQualiWinners(MatchModel $match, $qualifiedCount)
    {
        $results = collect($match->totalMatchResults);
        $results = $results->sortBy('result', SORT_NATURAL);
        $winners = $results->take($qualifiedCount);
        $winners = $winners->map(function (?MatchResult $result, $participantId) {
            return $participantId;
        });
        $arrayWithObject = array_values((array)$winners);
        return array_values((array)$arrayWithObject[0]);
    }

    private function giveOutCoins(MatchModel $final)
    {
        $isHourlyShowdown = $final->group->event->properties->filter(function (EventProperty $property) {
            return $property->key == EventProperty::HOURLY_SHOWDOWN;
        });

        if (!$isHourlyShowdown->count()) {
            return;
        }

        $winners = $this->getMatchWinners($final);
        $winner1ParticipantId = $winners[0];
        $winner2ParticipantId = $winners[1];

        /** @var Participant $winner1Participant */
        $winner1Participant = Participant::where('id', $winner1ParticipantId)->first();

        /** @var Participant $winner2Participant */
        $winner2Participant = Participant::where("id", $winner2ParticipantId)->first();

        $coinTransaction = new CoinTransaction();
        $coinTransaction->userId      = $winner1Participant->user->id;
        $coinTransaction->amount      = 50;
        $coinTransaction->description = "Hourly Showdown: 1st place";
        $coinTransaction->save();

        $winner1Participant->user->coins += 50;
        $winner1Participant->user->save();

        $coinTransaction = new CoinTransaction();
        $coinTransaction->userId      = $winner2Participant->user->id;
        $coinTransaction->amount      = 25;
        $coinTransaction->description = "Hourly Showdown: 2nd place";
        $coinTransaction->save();

        $winner2Participant->user->coins += 25;
        $winner2Participant->user->save();
    }
}
