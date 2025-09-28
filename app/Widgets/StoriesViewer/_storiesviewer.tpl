<ul class="list controls middle">
    <li>
        <span class="primary icon active" onclick="history.back()" title="{$c->__('button.close')}">
            <i class="material-symbols">arrow_back</i>
        </span>
    </li>
</ul>
<article class="story">
    <img class="main" src="{$story->picture->href|protectPicture}">
    <div class="next"><i class="material-symbols">chevron_right</i></div>
    <ul class="head list middle">
        <li>
            {if="$story->contact"}
                <span class="primary icon bubble small">
                    <img src="{$story->contact->getPicture()}">
                </span>
            {/if}
            {if="$story->isMine($c->me)"}
            <span class="control icon active" onclick="StoriesViewer.pause(); StoriesViewer_ajaxDelete('{$story->id}')">
                <i class="material-symbols fill">delete</i>
            </span>
            {/if}
            <span class="control icon active" onclick="StoriesViewer.pause(); SendTo_ajaxSendContact('{$story->getRef()}')">
                <i class="material-symbols">share</i>
            </span>
            <span class="control icon pause toggleable" onclick="StoriesViewer.start()">
                <i class="material-symbols fill">play_arrow</i>
            </span>
            <span class="control icon play toggleable" onclick="StoriesViewer.pause()">
                <i class="material-symbols fill">pause</i>
            </span>
            <div>
                <p class="line normal">
                    <a href="#" onclick="MovimUtils.reload('{$c->route('contact', $story->aid)}')">
                        {$story->truename}
                    </a>
                </p>
                <p>
                    {$count = $story->user_views_count}
                    {if="$count > 2"}
                        {$count} <i class="material-symbols">visibility</i> •
                    {/if}

                    {$story->published|prepareDate:true,true}
                </p>
            </div>
        </li>
    </ul>
    <ul class="list middle">
        <li>
            <div>
                <p class="normal title">{autoescape="off"}{$story->title|addHashtagsLinks}{/autoescape}</p>
            </div>
        </li>
        {if="!$story->isMine()$c->me"}
        <li class="comment">
            <span class="control icon active" onclick="StoriesViewer.sendComment({$story->id})">
                <i class="material-symbols">send</i>
            </span>
            <form name="storycomment" onsubmit="return false;">
                <div>
                    <input name="comment" autocomplete="off" type="text" {if="$story->contact"}placeholder="{$c->__('stories.comment', $story->contact->truename)}"{/if} onfocus="StoriesViewer.pause()" onblur="StoriesViewer_ajaxStart()">
                </div>
            </form>
        </li>
        {/if}
    </ul>
</article>