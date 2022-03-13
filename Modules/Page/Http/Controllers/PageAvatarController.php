<?php


namespace Modules\Page\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Page\Http\Requests\CreateAvatarRequest;


class PageAvatarController extends Controller
{
    /**
     * @param CreateAvatarRequest $request
     * @param $slug
     * @return false|string
     */
    public function update(CreateAvatarRequest $request, $slug)
    {
        $request->validated();

        $avatar   = $request->file('avatar');
        $path     = "pages/$slug";
        $fileName = $path."/avatar.jpg";

        if (Storage::exists($fileName)) {
            Storage::delete($fileName);
        }

        return Storage::disk("do_spaces")->putFileAs($path, $avatar, 'avatar.jpg', 'public');
    }

    /**
     * @param $slug
     * @return string
     */
    public function get($slug)
    {
        try {
            $path     = "pages/$slug";
            $fileName = $path."/avatar.jpg";
            return Storage::response($fileName);
        } catch (\Throwable $exception) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }
    }
}
