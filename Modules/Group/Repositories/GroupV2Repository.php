<?php


namespace Modules\Group\Repositories;


use Illuminate\Support\Facades\DB;
use Modules\Event\Entities\Participant;
use Modules\Group\Entities\Format;
use Modules\Group\Entities\Group;
use Modules\Page\Entities\Page;
use Modules\User\Entities\User;

class GroupV2Repository
{
    public function getEventGroups($eventId)
    {
        $groups = DB::table(Group::TABLE_NAME)
            ->where("event_id", $eventId)
            ->get();

        return $this->addRelations($groups);
    }

    public function getGroupsByContainer($containerId)
    {
        $groups = DB::table(Group::TABLE_NAME)
            ->where("group_container_id", $containerId)
            ->get();

        return $this->addRelations($groups);
    }

    private function addRelations($groups)
    {
        foreach ($groups as $group)
        {
            $formatRelations = DB::table("format_group")
                ->where("group_id", $group->id)
                ->get();

            $group->formats = [];
            foreach ($formatRelations as $relation) {
                $format = DB::table(Format::TABLE_NAME)
                    ->where("id", $relation->format_id)
                    ->first();

                $group->formats[] = $format;
            }

            $participantRelations = DB::table("group_participant")
                ->where("group_id", $group->id)
                ->get();

            $group->participants = [];
            foreach ($participantRelations as $participantRelation) {
                $participant = DB::table(Participant::TABLE_NAME)
                    ->where("id", $participantRelation->participant_id)
                    ->first();

                if (!$participant) {
                    continue;
                }

                if ($participant->type == 'user') {
                    $participant->user = DB::table(User::TABLE_NAME)
                        ->where("id", $participant->user_id)
                        ->first([
                            "id",
                            "nickname",
                            "elo",
                            "nat",
                            "tm_nickname",
                            "badge_id"
                        ]);
                }

                if ($participant->type == 'page') {
                    $participant->page = DB::table(Page::TABLE_NAME)
                        ->where("id", $participant->page_id)
                        ->first([
                            "id",
                            "name",
                            "slug",
                            "about",
                            "elo",
                            "created_at",
                            "updated_at",
                            "user_id",
                            "type_id"
                        ]);
                }

                $group->participants[] = $participant;
            }
        }

        return $groups;
    }
}
