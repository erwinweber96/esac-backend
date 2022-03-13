<?php


namespace Modules\Page\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Page\Entities\Article;
use Modules\Page\Entities\Page;
use Modules\Page\Http\Requests\CreateArticleRequest;
use Modules\User\Entities\User;

/**
 * Class ArticleController
 * @package Modules\Page\Http\Controllers
 */
class ArticleController
{
    public function getById($articleId)
    {
        return Article::where("id", $articleId)->first();
    }

    public function getBySlug($slug)
    {
        return Article::where("slug", $slug)
            ->with('page')
            ->first();
    }

    /**
     * Home page articles
     */
    public function get()
    {
        return Article::paginate(4);
    }

    /**
     * @param CreateArticleRequest $articleRequest
     * @return JsonResponse|Article
     */
    public function create(CreateArticleRequest $articleRequest)
    {
        /** @var User $user */
        $user = auth()->user();

        $page = DB::table(Page::TABLE_NAME)
            ->where('id', $articleRequest->pageId)
            ->first(["user_id"]);

        if ($page->user_id != $user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $articleRequest->validated();

        $article = new Article();

        $article->title = $articleRequest->title;
        $article->pageId = $articleRequest->pageId;
        $article->content = $articleRequest->content;
        $article->largeImgUrl = $articleRequest->largeImgUrl;
        $article->smallImgUrl = $articleRequest->smallImgUrl;
        $article->slug = Str::slug($articleRequest->title);

        try {
            $article->save();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => [
                    "message" => ["Could not create article."]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $article;
    }

    public function delete($articleId)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Article $article */
        $article = Article::where("id", $articleId)->first();

        $page = DB::table(Page::TABLE_NAME)
            ->where('id', $article->pageId)
            ->first(["user_id"]);

        if ($page->user_id != $user->id) {
            return response()->json([
                "errors" => [
                    "message" => ["Not Authorized."]
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $article->delete();
        } catch (\Throwable $exception) {
            return response()->json([
                "errors" => [
                    "message" => ["Could not delete article."]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Successfully deleted article."
        ], Response::HTTP_OK);
    }
}
