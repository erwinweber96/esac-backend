<?php


namespace Modules\Event\Services;


use App\Config\Csv;
use Illuminate\Support\Collection;
use Modules\Event\Entities\Event;
use Modules\Event\Entities\Participant;
use Modules\Game\Entities\Game;
use Modules\User\Entities\User;

/**
 * Class ParticipantExportService
 * @package Modules\Event\Services
 */
class ParticipantExportService
{
    const HEADER = [
        "participantId",
        "userId",
        "nickname",
        "login",
        "team"
    ];

    /**
     * @param Event $event
     * @return string
     */
    public function exportEventParticipantsToCsv(Event $event): string
    {
        /** @var Collection $participants */
        $participants = $event->participants;

        $csv = "";
        $csv = Csv::addRow($csv, self::HEADER);

        if ($event->isTeamEvent) {
            /** @var Participant $participant */
            foreach ($participants as $participant) {
                $page = $participant->page;
                foreach ($page->members as $member) {
                    $user = $member->user;
                    $csv  = Csv::addRow($csv, $this->getParticipantTeamData($participant, $user));
                }
            }

            return $csv;
        }

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $csv = Csv::addRow($csv, $this->getParticipantUserData($participant));
        }

        return $csv;
    }

    private function getParticipantUserData(Participant $participant)
    {
        return [
            $participant->id,
            $participant->user->id,
            $participant->user->nickname,
            $participant->inGameAccount(),
            ""
        ];
    }

    private function getParticipantTeamData(Participant $participant, User $user)
    {
        switch ($participant->event->game->name) {
            case Game::TRACKMANIA_2_STADIUM:
                $inGameAccount = $user->maniaplanet->login;
                break;
            case Game::TRACKMANIA:
                $inGameAccount = $user->tmNickname;
                break;
            default:
                $inGameAccount = "";
        }

        return [
            $participant->id,
            $user->id,
            $user->nickname,
            $inGameAccount,
            $participant->page->name
        ];
    }
}
