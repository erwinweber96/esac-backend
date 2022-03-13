<?php


namespace Modules\Event\Policies;


use Modules\Event\Entities\EventModeratorRole;

class EventPolicy extends EventModeratorParentPolicy
{
    public function change_event_status()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::CHANGE_EVENT_STATUS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_posts()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::CREATE_POSTS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function remove_participants()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::REMOVE_PARTICIPANTS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_faq()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::CREATE_FAQ)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_group()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::CREATE_GROUP)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_group()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_GROUP)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function delete_group()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::DELETE_GROUP)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_group_formats()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_GROUP_FORMATS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_group_participants()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_GROUP_PARTICIPANTS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_format()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::CREATE_FORMAT)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_format()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_FORMAT)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function delete_format()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::DELETE_FORMAT)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function add_game_server()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::ADD_GAME_SERVER)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function submit_game_server()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::SUBMIT_GAME_SERVER)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function moderate_game_servers()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::MODERATE_GAME_SERVERS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function add_map_pool()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::ADD_MAP_POOL)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_map_pool()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_MAP_POOL)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function delete_map_pool()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::DELETE_MAP_POOL)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function add_live_stream()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::ADD_LIVE_STREAM)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function submit_live_stream()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::SUBMIT_LIVE_STREAM)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function moderate_live_streams()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::MODERATE_LIVE_STREAMS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function create_match()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::CREATE_MATCH)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_match()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_MATCH)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function delete_match()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::DELETE_MATCH)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_match_participants()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_MATCH_PARTICIPANTS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function edit_match_formats()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::EDIT_MATCH_FORMATS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function add_total_result()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::ADD_TOTAL_RESULT)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function submit_total_result()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::SUBMIT_TOTAL_RESULT)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function moderate_results()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::MODERATE_RESULTS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function add_vod()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::ADD_VOD)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function submit_vod()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::SUBMIT_VOD)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }

    public function moderate_vods()
    {
        $allowed = EventModeratorRole::where("name", EventModeratorRole::MODERATE_VODS)
            ->where("moderator_id", $this->moderator->id)
            ->first();

        return (bool)$allowed;
    }
}
