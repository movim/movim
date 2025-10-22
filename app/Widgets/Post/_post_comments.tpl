{$liked = false}

{if="$post->likes->count() > 0"}
    <ul class="list divided spaced thin">
        <li>
            <span class="primary icon red tiny">
                <i class="material-symbols fill">favorite</i>
            </span>
            <div>
                <p class="all normal">
                    {loop="$post->likes"}
                        {if="$public"}
                            {$value->truename}{if="$key + 1 < $post->likes->count()"},{/if}
                        {else}
                            {if="$value->isMine($c->me)"}
                                {$liked = [$value->server, $value->node, $value->nodeid]}
                            {/if}
                            <a title="{$value->published|prepareDate:true,true}"
                               href="{$c->route('contact', $value->aid)}">
                                {$value->truename}</a>{if="$key + 1 < $post->likes->count()"},{/if}
                        {/if}
                    {/loop}
                </p>
            </div>
        </li>
    </ul>
{/if}

{if="!$public"}
    <hr />
    <ul class="list">
        <li>
            <div>
                <p class="center">
                    {if="$liked"}
                        <button class="button red flat"
                            id="like"
                            onclick="this.classList.add('disabled'); PostActions_ajaxDeleteConfirm('{$liked[0]}', '{$liked[1]}', '{$liked[2]}')">
                            <i class="material-symbols fill">favorite</i> {$post->likes->count()}
                        </button>
                    {else}
                        <button class="button red flat"
                            id="like"
                            onclick="this.classList.add('disabled'); PostActions_ajaxLike('{$post->server}', '{$post->node}', '{$post->nodeid}')">
                            <i class="material-symbols">favorite</i> {$post->likes->count()}
                        </button>
                    {/if}
                    <button class="button flat gray" onclick="Post.comment()">
                        <i class="material-symbols">add_comment</i> {$c->__('post.comment_add')}
                    </button>
                    <a
                        title="{$c->__('button.share')}"
                        class="button flat gray"
                        onclick="SendTo.shareArticle('{$post->getRef()}')"
                        href="#"
                    >
                        <i class="material-symbols">share</i> {$c->__('button.share')}
                    </a>
                    <a
                        title="{$c->__('button.send_to')}"
                        class="button flat gray"
                        onclick="SendTo_ajaxSendContact('{$post->getRef()}')"
                        href="#"
                    >
                        <i class="material-symbols">send</i> {$c->__('button.send_to')}
                    </a>
                </p>
            </div>
        </li>
    </ul>
    <ul class="list card shadow thick">
        <li class="block hide" id="comment_add">
            <span class="primary icon gray">
                <i class="material-symbols">add_comment</i>
            </span>
            <span class="control icon gray active" onclick="Post_ajaxPublishComment(MovimUtils.formToJson('comment'),'{$post->server}', '{$post->node}', '{$post->nodeid}'); this.classList.add('disabled');">
                <i class="material-symbols">send</i>
            </span>
            <form name="comment">
                <div>
                    <textarea
                        dir="auto"
                        data-autoheight="true"
                        name="comment"
                        placeholder="{$c->__('field.type_here')}"
                    ></textarea>
                    <label for="comment">{$c->__('post.comment_add')}</label>
                </div>
            </form>
        </li>
    </ul>
{/if}

<ul class="list divided spaced middle">
    {if="$post->comments->count() > 0"}
        <li class="subheader center">
            <div>
                <p>
                    <span class="info">{$post->comments->count()}</span> {$c->__('post.comments')}
                </p>
            </div>
        </li>
    {/if}

    {loop="$post->comments"}
        {if="$value->title"}
        <li id="{$value->nodeid|cleanupId}"
            {if="!$public && $value->isMine($c->me, true) && $value->isLike()"}class="mine"{/if}>
            {if="!$public && ($value->isMine($c->me) || $post->isMine($c->me))"}
                <span class="control icon gray active"
                      onclick="PostActions_ajaxDelete('{$value->server}', '{$value->node}', '{$value->nodeid}')">
                    <i class="material-symbols">delete</i>
                </span>
            {/if}
            {if="$value->contact"}
                <span class="primary icon bubble small">
                    {if="$public"}
                        <img src="{$value->contact->getPicture(\Movim\ImageSize::S)}">
                    {else}
                        <a href="{$c->route('contact', $value->contact->jid)}">
                            <img src="{$value->contact->getPicture(\Movim\ImageSize::S)}">
                        </a>
                    {/if}
                </span>
            {else}
                <span class="primary icon bubble {if="$value->aid"}color {$value->aid|stringToColor}{/if} small">
                    {if="$public"}
                        <i class="material-symbols">person</i>
                    {else}
                        <a href="{$c->route('contact', $value->aid)}">
                            <i class="material-symbols">person</i>
                        </a>
                    {/if}
                </span>
            {/if}
            <div>
                <p class="normal line">
                    <span class="info" title="{$value->published|prepareDate}">
                        {$value->published|prepareDate:true,true}
                    </span>
                    {if="$value->truename"}
                        {if="$public"}
                            {$value->truename}
                        {else}
                            <a href="{$c->route('contact', $value->aid)}">
                                {$value->truename}
                            </a>
                        {/if}
                    {else}
                        {$c->__('post.unknown_contact')}
                    {/if}
                </p>
                <p class="all">
                    {autoescape="off"}
                        {$value->title|addHashtagsLinks|prepareString}
                    {/autoescape}
                </p>
            </div>
        </li>
        {/if}
    {/loop}
</ul>
