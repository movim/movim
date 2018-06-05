{$liked = false}

{if="$post->likes()->count() > 0"}
    <ul class="list divided spaced middle">
        <li>
            <span class="primary icon red">
                <i class="material-icons">favorite"></i>
            </span>
            <p>{$post->likes()->count()}</span> {$c->__('button.like')}</p>
            <p class="all">
                {loop="$post->likes"}
                    {if="$public"}
                        {$value->truename}
                    {else}
                        {if="$value->isMine()"}
                            {$liked = [$value->server, $value->node, $value->nodeid]}
                        {/if}
                        <a title="{$value->published|strtotime|prepareDate:true,true}"
                           href="{$c->route('contact', $value->aid)}">
                            {$value->truename}</a>{/if}{if="$key + 1 < $post->likes()->count()"},{/if}
                {/loop}
            </p>
        </li>
    </ul>
{/if}

<ul class="list divided spaced middle">
    {if="$post->comments()->count() > 0"}
        <li class="subheader center">
            <p>
                <span class="info">{$post->comments()->count()}</span> {$c->__('post.comments')}
            </p>
        </li>
    {/if}

    {loop="$post->comments"}
        {if="$value->title || $value->contentraw"}
        <li id="{$value->nodeid|cleanupId}"
            {if="!$public && $value->isMine(true) && $value->isLike()"}class="mine"{/if}>
            {if="!$public && ($value->isMine() || $post->isMine())"}
                <span class="control icon gray active"
                      onclick="PostActions_ajaxDelete('{$value->server}', '{$value->node}', '{$value->nodeid}')">
                    <i class="material-icons">delete</i>
                </span>
            {/if}
            {if="$value->contact"}
                {$url = $value->contact->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble small">
                        {if="$public"}
                            <img src="{$url}">
                        {else}
                            <a href="{$c->route('contact', $value->contact->jid)}">
                                <img src="{$url}">
                            </a>
                        {/if}
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->contact->jid|stringToColor} small">
                        {if="$public"}
                            <i class="material-icons">person</i>
                        {else}
                            <a href="{$c->route('contact', $value->contact->jid)}">
                                <i class="material-icons">person</i>
                            </a>
                        {/if}
                    </span>
                {/if}
            {else}
                <span class="primary icon bubble color {$value->aid|stringToColor} small">
                    {if="$public"}
                        <i class="material-icons">person</i>
                    {else}
                        <a href="{$c->route('contact', $value->aid)}">
                            <i class="material-icons">person</i>
                        </a>
                    {/if}
                </span>
            {/if}
            <p class="normal line">
                <span class="info" title="{$value->published|strtotime|prepareDate}">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
                {if="$public"}
                    {$value->truename}
                {else}
                    <a href="{$c->route('contact', $value->aid)}">
                        {$value->truename}
                    </a>
                {/if}
            </p>
            <p class="all">
                {if="$value->contentraw"}
                    {$value->contentraw|addHashtagsLinks|addHFR}
                {else}
                    {$value->title|addUrls|addHashtagsLinks|nl2br}
                {/if}
            </p>
        </li>
        {/if}
    {/loop}

    {if="!$public"}
    <li class="hide" id="comment_add">
        <span class="primary icon small gray">
            <i class="material-icons">comment</i>
        </span>
        <span class="control icon gray active" onclick="Post_ajaxPublishComment(MovimUtils.formToJson('comment'),'{$post->server}', '{$post->node}', '{$post->nodeid}')">
            <i class="material-icons">send</i>
        </span>
        <form name="comment">
            <div>
                <textarea
                    oninput="MovimUtils.textareaAutoheight(this);"
                    name="comment"
                    placeholder="{$c->__('field.type_here')}"
                ></textarea>
                <label for="comment">{$c->__('post.comment_add')}</label>
            </div>
        </form>
    </li>

    <li>
        <p class="center">
            {if="$liked"}
                <button class="button red flat"
                    id="like"
                    onclick="this.classList.add('disabled'); PostActions_ajaxDeleteConfirm('{$liked[0]}', '{$liked[1]}', '{$liked[2]}')">
                    <i class="material-icons">favorite_border</i>
                </button>
            {else}
                <button class="button red flat"
                    id="like"
                    onclick="this.classList.add('disabled'); PostActions_ajaxLike('{$post->server}', '{$post->node}', '{$post->nodeid}')">
                    <i class="material-icons">favorite</i> {$c->__('button.like')}
                </button>
            {/if}
            <button class="button flat gray" onclick="Post.comment()">
                <i class="material-icons">comment</i> {$c->__('post.comment_add')}
            </button>
            {if="$c->getUser()->hasPubsub()"}
            <a class="button flat gray" onclick="Post.share()">
                <i class="material-icons">share</i> {$c->__('button.share')}
            </a>
            {/if}
        </p>
    </li>
    {/if}
</ul>

