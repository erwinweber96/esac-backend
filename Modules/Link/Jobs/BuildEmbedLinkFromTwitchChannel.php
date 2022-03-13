<?php


namespace Modules\Link\Jobs;


use Modules\Link\Builders\LinkBuilder;
use Modules\Match\Http\Requests\AddTwitchChannelToMatchRequest;

/**
 * Class BuildEmbedLinkFromTwitchChannel
 * @package Modules\Link\Jobs
 */
class BuildEmbedLinkFromTwitchChannel
{
    const PREFIX = "https://player.twitch.tv/?channel=";

    /** @var LinkBuilder $linkBuilder */
    private $linkBuilder;

    /**
     * BuildEmbedLinkFromTwitchChannel constructor.
     * @param LinkBuilder $linkBuilder
     */
    public function __construct(LinkBuilder $linkBuilder)
    {
        $this->linkBuilder = $linkBuilder->prepare();
    }

    /**
     * @param AddTwitchChannelToMatchRequest $request
     * @return LinkBuilder
     */
    public function execute(AddTwitchChannelToMatchRequest $request): LinkBuilder
    {
        $link = self::PREFIX . $request->input("channelName");

        return $this->linkBuilder
            ->setUrl($link)
            ->setName($request->input("name"))
            ->setPending($request->input("pending"));
    }
}
