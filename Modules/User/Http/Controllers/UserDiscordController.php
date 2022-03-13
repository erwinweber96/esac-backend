<?php


namespace Modules\User\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Discord;
use Modules\User\Entities\User;
use Modules\User\Http\Requests\CreateUserDiscordRequest;

class UserDiscordController
{
    public function createOrUpdate(CreateUserDiscordRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        if (!$user) {
            return response()->json(["errors" => [
                "message" => "Not Authorized."]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validated();

        $discord = Discord::where("user_id", $user->id)->get();

        if (!$discord->count()) {
            $discord = new Discord();
        } else {
            /** @var Discord $discord */
            $discord = $discord->first();
        }

        $discord->userId            = $user->id;
        $discord->discordNickname   = $request->nickname;
        $discord->discordId         = intval($request->id);

        try {
            $discord->save();
        } catch (\Throwable $exception) {
            return response()->json(["errors" => [
                "message" => "Could not create discord."]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(["message" => "Successfully saved discord"]);
    }
}
