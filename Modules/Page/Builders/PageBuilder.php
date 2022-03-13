<?php


namespace Modules\Page\Builders;


use App\Model\Builder;
use App\Services\PseudoCrypt;
use Illuminate\Support\Str;

/**
 * Class PageBuilder
 * @package Modules\Page\Builders
 */
class PageBuilder implements Builder
{
    /** @var array */
    private $pageData;

    public function prepare(): Builder
    {
        $this->pageData = [];
        return $this;
    }

    public function setName(string $name, bool $updateSlug = true): self
    {
        $this->pageData['name'] = $name;

        if ($updateSlug) {
            $this->pageData['slug'] = Str::slug($name);
            $this->pageData['invite_token'] = PseudoCrypt::hash($this->pageData['slug']);
        }

        return $this;
    }

    public function setAbout(string $about): self
    {
        $this->pageData['about'] = $about;
        return $this;
    }

    public function setType(int $type): self
    {
        $this->pageData['type_id'] = $type;
        return $this;
    }

    public function withUserId(int $userId): self
    {
        $this->pageData['user_id'] = $userId;
        return $this;
    }

    public function setPrivate(bool $private): self
    {
        $this->pageData['private'] = $private;
        return $this;
    }

    public function build()
    {
        return $this->pageData;
    }
}
