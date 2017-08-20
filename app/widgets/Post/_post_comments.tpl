<ul class="list divided spaced middle">
    {if="count($comments) > 0"}
        <li class="subheader center">
            <p>
                <span class="info">{$comments|count}</span> {$c->__('post.comments')}
            </p>
        </li>
    {/if}

    {loop="$comments"}
        {if="$value->title || $value->contentraw"}
        <li id="{$value->nodeid|cleanupId}" {if="$value->isMine(true)"}class="mine"{/if}>
            {if="$value->isMine()"}
                <span class="control icon gray active"
                      onclick="PostActions_ajaxDelete('{$value->origin}', '{$value->node}', '{$value->nodeid}')">
                    <i class="zmdi zmdi-delete"></i>
                </span>
            {/if}

            {if="$value->isLike()"}
                <span class="primary icon small red">
                    <i class="zmdi zmdi-favorite"></i>
                </span>
            {else}
                {$url = $value->getContact()->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble small">
                        <a href="{$c->route('contact', $value->getContact()->jid)}">
                            <img src="{$url}">
                        </a>
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->getContact()->jid|stringToColor} small">
                        <a href="{$c->route('contact', $value->getContact()->jid)}">
                            <i class="zmdi zmdi-account"></i>
                        </a>
                    </span>
                {/if}
            {/if}
            <p class="normal line">
                <span class="info" title="{$value->published|strtotime|prepareDate}">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
                <a href="{$c->route('contact', $value->getContact()->jid)}">
                    {$value->getContact()->getTrueName()}
                </a>
            </p>
            {if="!$value->isLike()"}
                <p class="all">
                    {if="$value->contentraw"}
                        {$value->contentraw|addHashtagsLinks|addHFR}
                    {else}
                        {$value->title|addUrls|addHashtagsLinks|nl2br}
                    {/if}
                </p>
            {/if}
        </li>
        {/if}
    {/loop}

    <li class="hide" id="comment_add">
        <span class="primary icon small gray">
            <i class="zmdi zmdi-comment"></i>
        </span>
        <span class="control icon gray active" onclick="Post_ajaxPublishComment(MovimUtils.formToJson('comment'),'{$post->origin}', '{$post->node}', '{$post->nodeid}')">
            <i class="zmdi zmdi-mail-send"></i>
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
            {if="!$liked"}
            <button class="button red flat" id="like" onclick="MovimUtils.addClass('#like', 'disabled'); PostActions_ajaxLike('{$post->origin}', '{$post->node}', '{$post->nodeid}')">
                <i class="zmdi zmdi-favorite"></i> {$c->__('button.like')}
            </button>
            {/if}
            <button class="button flat gray" onclick="Post.comment()">
                <i class="zmdi zmdi-comment"></i> {$c->__('post.comment_add')}
            </button>
            {if="$c->supported('pubsub')"}
            <button class="button flat gray" onclick="Post.share()">
                <i class="zmdi zmdi-mail-reply"></i> {$c->__('button.reply')}
            </button>
            {/if}
        </p>
    </li>
</ul>

